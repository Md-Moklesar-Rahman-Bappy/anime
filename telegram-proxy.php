<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/TelegramBot.php';

$file_id = $_GET['file_id'] ?? $_GET['fid'] ?? '';
if (empty($file_id)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'file_id required']);
    exit;
}

$cache_dir = __DIR__ . '/telegram-cache';
$local_path = $cache_dir . '/' . $file_id . '.mp4';

// 1) Serve from local cache if exists
if (file_exists($local_path)) {
    $size = filesize($local_path);
    $fp = fopen($local_path, 'rb');
    if (!$fp) { http_response_code(500); exit; }
    $mime = mime_content_type($local_path) ?: 'video/mp4';
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . $size);
    header('Accept-Ranges: bytes');
    header('Cache-Control: public, max-age=86400');
    // Range support
    if (isset($_SERVER['HTTP_RANGE'])) {
        preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $m);
        $start = (int)$m[1];
        $end = $m[2] !== '' ? (int)$m[2] : $size - 1;
        fseek($fp, $start);
        http_response_code(206);
        header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
        header('Content-Length: ' . ($end - $start + 1));
    }
    fpassthru($fp);
    fclose($fp);
    exit;
}

// 2) Try Telegram API — get file_path and cache locally
$bot = TelegramBot::getInstance();
$file = $bot->getFile($file_id);
$file_path = $file['result']['file_path'] ?? null;

if ($file_path) {
    $dl_url = $bot->getFileUrl($file_path);

    // Cache file_path in DB for future redirects
    if ($file['result']['file_unique_id'] ?? null) {
        DB::execute(
            "UPDATE telegram_videos SET file_path = ? WHERE file_id = ? OR file_unique_id = ?",
            [$file_path, $file_id, $file['result']['file_unique_id']]
        );
    }

    // Download to local cache in the background + redirect for immediate playback
    if (!is_dir($cache_dir)) mkdir($cache_dir, 0777, true);
    $ctx = stream_context_create(['http' => ['timeout' => 300]]);
    $remote = @fopen($dl_url, 'rb', false, $ctx);
    if ($remote) {
        $local = @fopen($local_path, 'wb');
        if ($local) {
            // Spawn non-blocking background download
            stream_set_blocking($remote, false);
            $chunk = fread($remote, 8192);
            if ($chunk !== false && strlen($chunk) > 0) {
                fwrite($local, $chunk);
                // Save remaining in background via register_shutdown_function
                register_shutdown_function(function() use ($remote, $local) {
                    while (!feof($remote)) {
                        $c = fread($remote, 65536);
                        if ($c === false || strlen($c) === 0) break;
                        fwrite($local, $c);
                    }
                    fclose($local);
                    fclose($remote);
                });
            } else {
                fclose($local);
                fclose($remote);
                // Download failed, fall through to redirect
            }
        } else {
            fclose($remote);
        }
    }

    // Redirect to Telegram CDN for immediate streaming
    header('Location: ' . $dl_url);
    exit;
}

// 3) Check if message_id is stored — generate t.me link as fallback
$vid = DB::fetch("SELECT chat_id, message_id, assigned_to_episode_id FROM telegram_videos WHERE file_id = ?", [$file_id]);
if ($vid && $vid['chat_id'] && $vid['message_id']) {
    $chat_id_str = ltrim($vid['chat_id'], '-');
    $tme_url = 'https://t.me/c/' . $chat_id_str . '/' . $vid['message_id'];
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'File too large for direct streaming',
        'message' => 'This video exceeds Telegram\'s 20MB streaming limit. View it directly on Telegram.',
        'telegram_url' => $tme_url
    ]);
    exit;
}

http_response_code(404);
header('Content-Type: application/json');
echo json_encode(['error' => 'File not found or inaccessible']);
