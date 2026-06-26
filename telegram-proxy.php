<?php
// Proxies Telegram video files to hide bot token
// Usage: telegram-proxy.php?file_id=xxx  or  telegram-proxy.php?fid=xxx

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

// Try DB cache first
$cached = DB::fetch("SELECT file_path FROM telegram_videos WHERE file_id = ? OR file_unique_id = ?", [$file_id, $file_id]);
if ($cached && $cached['file_path']) {
    $bot = TelegramBot::getInstance();
    $url = $bot->getFileUrl($cached['file_path']);
    header('Location: ' . $url);
    exit;
}

// Fallback: fetch from API
$bot = TelegramBot::getInstance();
$info = $bot->getVideoInfo($file_id);
if (!$info) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'File not found or inaccessible']);
    exit;
}

// Cache it
DB::execute(
    "INSERT INTO telegram_videos (file_id, file_unique_id, file_path, file_size)
     VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE file_path = VALUES(file_path), file_size = VALUES(file_size)",
    [$info['file_id'], $info['file_unique_id'], $info['file_path'], $info['file_size']]
);

header('Location: ' . $info['stream_url']);
exit;
