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

// Helper: extract video info from a message array and save to DB
function save_video_from_message(array $msg_data, string $source_chat_id, string $from_id, string $from_username): bool {
    $video_data = null;
    $file_id = null;
    if ($msg_data['video'] ?? null) {
        $video_data = $msg_data['video'];
        $file_id = $video_data['file_id'];
    } elseif (($msg_data['document'] ?? null) && str_starts_with($msg_data['document']['mime_type'] ?? '', 'video/')) {
        $video_data = $msg_data['document'];
        $file_id = $video_data['file_id'];
    }
    if (!$video_data || !$file_id) return false;
    $file_name = $video_data['file_name'] ?? ($file_id . '.mp4');
    try {
        $existing = DB::fetch("SELECT id FROM telegram_videos WHERE file_id = ?", [$file_id]);
        if (!$existing) {
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
                    $msg_data['caption'] ?? '',
                    $source_chat_id,
                    $from_id,
                    $from_username,
                ]
            );
        }
    } catch (Exception $e) {}
    return true;
}

// ---- Handle channel_post (bot is admin in a channel) ----
$channel_post = $update['channel_post'] ?? null;
if ($channel_post) {
    $channel_chat_id = $channel_post['chat']['id'] ?? '';
    $channel_username = $channel_post['chat']['username'] ?? '';
    $post_author_id = $channel_post['sender_chat']['id'] ?? $channel_chat_id;
    $post_author_name = $channel_post['sender_chat']['username'] ?? $channel_username ? "@$channel_username" : "channel";
    save_video_from_message($channel_post, $channel_chat_id, $post_author_id, $post_author_name);
    exit;
}

// ---- Regular message handling ----
$is_channel_or_group = str_starts_with((string)$chat_id, '-');
if (!$is_channel_or_group) {
    $username = $update['message']['from']['username'] ?? '';
    $first_name = $update['message']['from']['first_name'] ?? '';
    DB::execute(
        "INSERT INTO telegram_subscribers (chat_id, username, first_name) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE username = VALUES(username), first_name = VALUES(first_name), active = 1",
        [$chat_id, $username, $first_name]
    );
}

// ---- Handle video messages (forwarded or direct) ----
$msg_data = $update['message'] ?? [];
$saved = save_video_from_message($msg_data, $chat_id, $update['message']['from']['id'] ?? '', $update['message']['from']['username'] ?? '');
if ($saved) {
    $file_id = ($msg_data['video']['file_id'] ?? $msg_data['document']['file_id'] ?? '');
    $file_name = ($msg_data['video']['file_name'] ?? $msg_data['document']['file_name'] ?? $file_id . '.mp4');
    $bot->sendMessage($chat_id,
        "📹 <b>Video received!</b>\n\n"
        . "File: <code>{$file_name}</code>\n"
        . "File ID: <code>{$file_id}</code>\n\n"
        . "Stream URL:\n"
        . BASE_URL . "/telegram-proxy.php?file_id=" . urlencode($file_id) . "\n\n"
        . "Admin panel: " . BASE_URL . "/admin/telegram-videos.php"
    );
    exit;
}

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
            . "/videos - List imported videos\n"
            . "/help - Show this help\n"
            . "/stop - Unsubscribe from notifications\n\n"
            . "<b>Tip:</b> Forward a video to me and I'll save it for the admin to attach to an episode!"
        );
        break;

    case '/help':
        $bot->sendMessage($chat_id,
            "<b>Anikoto Bot Commands</b>\n\n"
            . "🔍 <code>/anime &lt;name&gt;</code> - Search anime\n"
            . "📺 <code>/latest</code> - Latest 5 episodes\n"
            . "📹 <code>/videos</code> - List imported videos\n"
            . "ℹ️ <code>/help</code> - Show help\n"
            . "🚫 <code>/stop</code> - Unsubscribe\n\n"
            . "📹 <b>Video Import:</b> Forward any video to me and I'll save it to the library!\n\n"
            . "Visit: " . BASE_URL
        );
        break;

    case '/stop':
        DB::execute("UPDATE telegram_subscribers SET active = 0 WHERE chat_id = ?", [$chat_id]);
        $bot->sendMessage($chat_id, "You've been unsubscribed from notifications. Send /start to resubscribe.");
        break;

    case '/videos':
        $vids = DB::fetchAll(
            "SELECT id, file_name, file_id, created_at, assigned_to_episode_id
             FROM telegram_videos ORDER BY created_at DESC LIMIT 10"
        );
        if (empty($vids)) {
            $bot->sendMessage($chat_id, "No videos in the library. Forward a video to me to add it.");
            break;
        }
        $msg = "<b>Recent Videos</b>\n\n";
        foreach ($vids as $v) {
            $status = $v['assigned_to_episode_id'] ? '✅ Assigned' : '⬜ Unassigned';
            $msg .= "• <code>" . htmlspecialchars($v['file_name'] ?: 'video') . "</code> {$status}\n";
            $msg .= "  " . BASE_URL . "/telegram-proxy.php?fid=" . urlencode($v['file_id']) . "\n";
        }
        $msg .= "\nAdmin: " . BASE_URL . "/admin/telegram-videos.php";
        $bot->sendMessage($chat_id, $msg);
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
