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
    echo json_encode(['error' => 'Invalid Telegram URL. Use format: https://t.me/channelname/123']);
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

$subscriber = DB::fetch("SELECT chat_id FROM telegram_subscribers WHERE active = 1 ORDER BY created_at ASC LIMIT 1");
if (!$subscriber) {
    http_response_code(400);
    echo json_encode(['error' => 'No active subscribers. Send /start to @' . TELEGRAM_BOT_USERNAME . ' first.']);
    exit;
}

$result = $bot->forwardMessage('@' . $channel, $msg_id, $subscriber['chat_id']);
if (!$result) {
    $result = $bot->copyMessage('@' . $channel, $msg_id, $subscriber['chat_id'], ['disable_notification' => true]);
}
if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to forward message. Ensure @' . TELEGRAM_BOT_USERNAME . ' is admin of @' . $channel]);
    exit;
}

sleep(1);

$updates = $bot->getUpdates(0, 10, ['message']);

$saved_video = null;
$max_offset = 0;

foreach ($updates['result'] ?? [] as $update) {
    $uid = $update['update_id'];
    if ($uid > $max_offset) $max_offset = $uid;

    $msg = $update['message'] ?? [];
    $msg_chat_id = $msg['chat']['id'] ?? '';
    if ((string)$msg_chat_id !== (string)$subscriber['chat_id']) continue;

    $video_data = $msg['video'] ?? $msg['document'] ?? null;
    if (!$video_data) continue;

    $file_id = $video_data['file_id'] ?? '';
    if (!$file_id) continue;

    $existing = DB::fetch("SELECT id FROM telegram_videos WHERE file_id = ?", [$file_id]);
    if ($existing) {
        $saved_video = DB::fetch("SELECT * FROM telegram_videos WHERE id = ?", [$existing['id']]);
        break;
    }

    $file_name = $video_data['file_name'] ?? ($file_id . '.mp4');
    DB::insert(
        "INSERT INTO telegram_videos (file_id, file_unique_id, file_size, file_name, duration, mime_type, width, height, thumbnail, caption, chat_id, from_user_id, from_username)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
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
            $msg['caption'] ?? '',
            $subscriber['chat_id'],
            'admin',
            'admin'
        ]
    );
    $new_id = DB::lastInsertId();
    $saved_video = DB::fetch("SELECT * FROM telegram_videos WHERE id = ?", [$new_id]);
    break;
}

if ($max_offset > 0) {
    $bot->getUpdates($max_offset + 1, 1);
}

if ($saved_video) {
    echo json_encode([
        'success' => true,
        'video_id' => $saved_video['id'],
        'file_id' => $saved_video['file_id'],
        'file_name' => $saved_video['file_name'],
        'proxy_url' => BASE_URL . '/telegram-proxy.php?fid=' . urlencode($saved_video['file_id']),
        'duration' => $saved_video['duration'],
        'width' => $saved_video['width'],
        'height' => $saved_video['height'],
    ]);
} else {
    echo json_encode([
        'success' => true,
        'pending' => true,
        'message' => 'Video forwarded. It will appear in the list shortly — refresh the Telegram Videos page.',
    ]);
}
