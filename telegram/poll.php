<?php
/**
 * Telegram polling script — run this via cron or manually to process updates
 * when webhook cannot be used (e.g., localhost without HTTPS).
 * Usage: php telegram/poll.php
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/TelegramBot.php';

$bot = TelegramBot::getInstance();

// Get the last processed update ID from a file
$offset_file = __DIR__ . '/poll_offset.txt';
$offset = 0;
if (file_exists($offset_file)) {
    $offset = (int)file_get_contents($offset_file);
}

$updates = $bot->getUpdates($offset, 100, ['message', 'callback_query', 'channel_post']);
if (!$updates || empty($updates['result'])) {
    echo "No new updates.\n";
    exit;
}

$last_id = $offset;
foreach ($updates['result'] as $update) {
    $update_id = $update['update_id'];
    if ($update_id <= $offset) continue;

    // Re-route to webhook handler
    $_POST = [];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $GLOBALS['telegram_update'] = $update;

    // Simulate webhook processing
    require __DIR__ . '/webhook.php';

    $last_id = $update_id;
}

// Save offset
file_put_contents($offset_file, $last_id + 1);
echo "Processed up to update #{$last_id}\n";
