<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/TelegramBot.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['url'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing URL parameter.']);
    exit;
}

$url = trim($_POST['url']);
if (!preg_match('#t\.me/([a-z0-9_]+)/(\d+)#i', $url, $m)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid URL. Use: https://t.me/channelname/123']);
    exit;
}

$channel = $m[1];
$msg_id = (int)$m[2];

$bot = TelegramBot::getInstance();

$chat_info = $bot->getChat('@' . $channel);
if (!$chat_info || !isset($chat_info['result']['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Channel @' . $channel . ' not found or bot is not a member.']);
    exit;
}

$file_id = null;
$video_data = null;
$source_label = '';

// Strategy 1: forward message to the same channel, capture file_id, delete duplicate
$result = $bot->forwardMessage('@' . $channel, $msg_id, '@' . $channel);
if ($result) {
    $forwarded_msg = $result['result'] ?? [];
    $video_data = $forwarded_msg['video'] ?? $forwarded_msg['document'] ?? null;
    $file_id = $video_data['file_id'] ?? null;
    $new_msg_id = $forwarded_msg['message_id'] ?? 0;
    if ($new_msg_id) {
        $bot->call('deleteMessage', ['chat_id' => '@' . $channel, 'message_id' => $new_msg_id]);
    }
    $source_label = 'channel_self';
}

// Strategy 2: forward to a subscriber's DM (fallback)
if (!$file_id) {
    $subscriber = DB::fetch("SELECT chat_id FROM telegram_subscribers WHERE active = 1 ORDER BY created_at ASC LIMIT 1");
    if ($subscriber) {
        $result = $bot->forwardMessage('@' . $channel, $msg_id, $subscriber['chat_id']);
        if (!$result) {
            $result = $bot->copyMessage('@' . $channel, $msg_id, $subscriber['chat_id'], ['disable_notification' => true]);
        }
        if ($result) {
            $forwarded_msg = $result['result'] ?? [];
            $video_data = $forwarded_msg['video'] ?? $forwarded_msg['document'] ?? null;
            $file_id = $video_data['file_id'] ?? null;
            $source_label = 'subscriber_forward';
        }
    }
}

if (!$file_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Could not retrieve video. The bot must be admin of @' . $channel . ' with can_delete_messages rights. If the channel self-forward fails, you also need a subscriber (send /start to @' . TELEGRAM_BOT_USERNAME . ', then click "Run Poll Now").']);
    exit;
}

$file_name = $video_data['file_name'] ?? ($file_id . '.mp4');

$existing = DB::fetch("SELECT id FROM telegram_videos WHERE file_id = ?", [$file_id]);
if ($existing) {
    $saved_video = DB::fetch("SELECT * FROM telegram_videos WHERE id = ?", [$existing['id']]);
} else {
    DB::insert(
        "INSERT INTO telegram_videos (file_id, file_unique_id, file_size, file_name, duration, mime_type, width, height, thumbnail, caption, chat_id, from_user_id, from_username, message_id)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
        [
            $file_id,
            $video_data['file_unique_id'] ?? '',
            $video_data['file_size'] ?? 0,
            $file_name,
            $video_data['duration'] ?? null,
            $video_data['mime_type'] ?? '',
            $video_data['width'] ?? null,
            $video_data['height'] ?? null,
            $video_data['thumbnail']['file_id'] ?? '',
            '',
            $channel,
            'admin',
            'admin',
            $msg_id,
        ]
    );
    $new_id = DB::lastInsertId();
    $saved_video = DB::fetch("SELECT * FROM telegram_videos WHERE id = ?", [$new_id]);

    // Eagerly cache file_path
    $file_info = $bot->getFile($file_id);
    $file_path = $file_info['result']['file_path'] ?? null;
    if ($file_path) {
        DB::execute("UPDATE telegram_videos SET file_path = ? WHERE id = ?", [$file_path, $new_id]);
        $cache_dir = __DIR__ . '/../telegram-cache';
        $local_path = $cache_dir . '/' . $file_id . '.mp4';
        if (!is_dir($cache_dir)) @mkdir($cache_dir, 0777, true);
        if (!file_exists($local_path)) {
            $dl_url = $bot->getFileUrl($file_path);
            $ctx = stream_context_create(['http' => ['timeout' => 300]]);
            $remote = @fopen($dl_url, 'rb', false, $ctx);
            if ($remote) {
                $local = @fopen($local_path, 'wb');
                if ($local) {
                    stream_set_blocking($remote, false);
                    $first = fread($remote, 8192);
                    if ($first !== false && strlen($first) > 0) {
                        fwrite($local, $first);
                        register_shutdown_function(function() use ($remote, $local) {
                            while (!feof($remote)) {
                                $c = fread($remote, 65536);
                                if ($c === false || strlen($c) === 0) break;
                                fwrite($local, $c);
                            }
                            fclose($local);
                            fclose($remote);
                        });
                    } else { fclose($local); fclose($remote); }
                } else { fclose($remote); }
            }
        }
    }
}

echo json_encode([
    'success' => true,
    'video_id' => $saved_video['id'],
    'file_id' => $saved_video['file_id'],
    'file_name' => $saved_video['file_name'],
    'proxy_url' => BASE_URL . '/telegram-proxy.php?fid=' . urlencode($saved_video['file_id']),
    'telegram_url' => 'https://t.me/' . $channel . '/' . $msg_id,
    'duration' => $saved_video['duration'],
    'width' => $saved_video['width'],
    'height' => $saved_video['height'],
]);
