<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/TelegramBot.php';

$bot = TelegramBot::getInstance();

// Support both webhook (POST) and polling (global variable)
$update = $bot->parseWebhookInput();
if (!$update && isset($GLOBALS['telegram_update'])) {
    $update = $GLOBALS['telegram_update'];
}

if (!$update) {
    http_response_code(400);
    exit;
}

$chat_id = $bot->getChatId();
$text = $bot->getText();

if (!$chat_id) exit;

// Ensure telegram_subscribers table exists
try {
    DB::query("CREATE TABLE IF NOT EXISTS `telegram_subscribers` (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `chat_id` VARCHAR(100) NOT NULL,
        `username` VARCHAR(255) DEFAULT NULL,
        `first_name` VARCHAR(255) DEFAULT NULL,
        `active` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `chat_id_unique` (`chat_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Exception $e) {}

$username = $update['message']['from']['username'] ?? '';
$first_name = $update['message']['from']['first_name'] ?? '';

// Subscribe/register the user
DB::execute(
    "INSERT INTO telegram_subscribers (chat_id, username, first_name) VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE username = VALUES(username), first_name = VALUES(first_name), active = 1",
    [$chat_id, $username, $first_name]
);

if (!$text) exit;

$text_lower = mb_strtolower(trim($text));
$parts = explode(' ', $text_lower);
$command = $parts[0];

switch ($command) {
    case '/start':
        $bot->sendMessage($chat_id,
            "👋 <b>Welcome to Anikoto Anime!</b>\n\n"
            . "You'll now receive notifications about new episodes and updates.\n\n"
            . "<b>Commands:</b>\n"
            . "/anime &lt;title&gt; - Search anime\n"
            . "/latest - Latest episodes\n"
            . "/help - Show this help\n"
            . "/stop - Unsubscribe from notifications"
        );
        break;

    case '/help':
        $bot->sendMessage($chat_id,
            "<b>Anikoto Bot Commands</b>\n\n"
            . "🔍 <code>/anime &lt;name&gt;</code> - Search anime\n"
            . "📺 <code>/latest</code> - Latest 5 episodes\n"
            . "ℹ️ <code>/help</code> - Show help\n"
            . "🚫 <code>/stop</code> - Unsubscribe\n\n"
            . "Visit: " . BASE_URL
        );
        break;

    case '/stop':
        DB::execute("UPDATE telegram_subscribers SET active = 0 WHERE chat_id = ?", [$chat_id]);
        $bot->sendMessage($chat_id, "You've been unsubscribed from notifications. Send /start to resubscribe.");
        break;

    case '/latest':
        $eps = DB::fetchAll(
            "SELECT e.title as ep_title, e.number, e.created_at, a.title, a.slug, a.thumbnail
             FROM episodes e INNER JOIN anime a ON a.id = e.anime_id
             ORDER BY e.created_at DESC LIMIT 5"
        );
        if (empty($eps)) {
            $bot->sendMessage($chat_id, "No episodes available yet.");
            break;
        }
        $msg = "<b>Latest Episodes</b>\n\n";
        foreach ($eps as $ep) {
            $url = BASE_URL . '/watch/' . $ep['slug'] . '?ep=' . $ep['number'];
            $msg .= "• <a href=\"{$url}\">{$ep['title']} Ep {$ep['number']}</a>\n";
        }
        $bot->sendMessage($chat_id, $msg);
        break;

    case '/anime':
        $search = implode(' ', array_slice($parts, 1));
        if (empty($search)) {
            $bot->sendMessage($chat_id, "Usage: <code>/anime &lt;name&gt;</code>\nExample: <code>/anime One Piece</code>");
            break;
        }
        $results = DB::fetchAll(
            "SELECT title, slug, type, episodes_count, rating, thumbnail FROM anime WHERE title LIKE ? LIMIT 5",
            ['%' . $search . '%']
        );
        if (empty($results)) {
            $bot->sendMessage($chat_id, "No results for \"" . htmlspecialchars($search) . "\". Try Jujutsu Kaisen, Naruto, One Piece...");
            break;
        }
        $msg = "<b>Search results for: " . htmlspecialchars($search) . "</b>\n\n";
        foreach ($results as $r) {
            $url = BASE_URL . '/' . $r['slug'];
            $msg .= "• <a href=\"{$url}\">{$r['title']}</a>"
                  . ($r['type'] ? " [{$r['type']}]" : '')
                  . ($r['rating'] ? " ⭐{$r['rating']}" : '')
                  . "\n";
        }
        $bot->sendMessage($chat_id, $msg);
        break;

    default:
        // Unknown command — search as anime
        $results = DB::fetchAll(
            "SELECT title, slug, type, thumbnail FROM anime WHERE title LIKE ? LIMIT 3",
            ['%' . $text . '%']
        );
        if (!empty($results)) {
            $msg = "<b>Did you mean?</b>\n\n";
            foreach ($results as $r) {
                $url = BASE_URL . '/' . $r['slug'];
                $msg .= "• <a href=\"{$url}\">{$r['title']}</a>\n";
            }
            $bot->sendMessage($chat_id, $msg);
        } else {
            $bot->sendMessage($chat_id, "Unknown command. Send /help to see available commands.");
        }
        break;
}
