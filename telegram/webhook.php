<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/TelegramBot.php';

$bot = TelegramBot::getInstance();

$update = $bot->parseWebhookInput();
if (!$update && isset($GLOBALS['telegram_update'])) {
    $update = $GLOBALS['telegram_update'];
}
if (!$update) { http_response_code(400); exit; }

$chat_id = $bot->getChatId();
$text = $bot->getText();
if (!$chat_id) exit;

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

// ─── Helpers ────────────────────────────────────────────────────────────────

function save_video_from_message(array $msg_data, string $source_chat_id, string $from_id, string $from_username): int|false {
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
        if ($existing) return (int)$existing['id'];
        $new_id = DB::insert(
            "INSERT INTO telegram_videos (file_id, file_unique_id, file_size, file_name, duration, mime_type, width, height, thumbnail, caption, chat_id, from_user_id, from_username, message_id)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            [$file_id, $video_data['file_unique_id'] ?? '', $video_data['file_size'] ?? 0, $file_name,
             $video_data['duration'] ?? null, $video_data['mime_type'] ?? '', $video_data['width'] ?? null,
             $video_data['height'] ?? null, $video_data['thumbnail']['file_id'] ?? '', $msg_data['caption'] ?? '',
             $source_chat_id, $from_id, $from_username, $msg_data['message_id'] ?? null]
        );
        if (!$new_id) return false;

        // Eagerly cache file_path from Telegram API
        $bot = TelegramBot::getInstance();
        $file_info = $bot->getFile($file_id);
        $file_path = $file_info['result']['file_path'] ?? null;
        if ($file_path) {
            DB::execute("UPDATE telegram_videos SET file_path = ? WHERE id = ?", [$file_path, $new_id]);

            // Start local download in background
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
        return $new_id;
    } catch (Exception $e) { return false; }
}

function match_anime_episode_from_caption(string $caption): ?array {
    $caption = trim($caption);
    if (!$caption) return null;

    // Strategy 1: "Title Episode 12" or "Title Ep 12" or "Title #12"
    if (preg_match('/^(.+?)\s+(?:ep|episode|#)\s*(\d+)$/i', $caption, $m)) {
        $search = trim($m[1]);
        $ep_num = (int)$m[2];
        $anime = DB::fetch("SELECT id, title FROM anime WHERE title LIKE ? LIMIT 1", ['%' . $search . '%']);
        if ($anime) {
            $ep = DB::fetch("SELECT id FROM episodes WHERE anime_id = ? AND number = ?", [$anime['id'], $ep_num]);
            return [
                'anime_id' => $anime['id'], 'anime_title' => $anime['title'],
                'episode_id' => $ep ? (int)$ep['id'] : null, 'episode_number' => $ep_num,
                'confidence' => $ep ? 0.95 : 0.7
            ];
        }
    }

    // Strategy 2: "Title ep12", "Title#12", "Title 12 sub/dub"
    if (preg_match('/^(.+?)\s*[ep#]?\s*(\d+)\s*(sub|dub)?$/i', $caption, $m)) {
        $search = trim($m[1]);
        $ep_num = (int)$m[2];
        $anime = DB::fetch("SELECT id, title FROM anime WHERE title LIKE ? LIMIT 1", ['%' . $search . '%']);
        if ($anime) {
            $ep = DB::fetch("SELECT id FROM episodes WHERE anime_id = ? AND number = ?", [$anime['id'], $ep_num]);
            return [
                'anime_id' => $anime['id'], 'anime_title' => $anime['title'],
                'episode_id' => $ep ? (int)$ep['id'] : null, 'episode_number' => $ep_num,
                'confidence' => $ep ? 0.9 : 0.65
            ];
        }
    }

    // Strategy 3: Just "/14" or "14" — infer from last imported anime
    if (preg_match('/^\/?(\d+)$/', $caption, $m)) {
        $ep_num = (int)$m[1];
        $last = DB::fetch(
            "SELECT a.id, a.title FROM anime a
             INNER JOIN episodes e ON e.anime_id = a.id
             INNER JOIN telegram_videos tv ON tv.assigned_to_episode_id = e.id
             ORDER BY tv.created_at DESC LIMIT 1"
        );
        if (!$last) {
            $last = DB::fetch("SELECT id, title FROM anime ORDER BY updated_at DESC LIMIT 1");
        }
        if ($last) {
            $ep = DB::fetch("SELECT id FROM episodes WHERE anime_id = ? AND number = ?", [$last['id'], $ep_num]);
            return [
                'anime_id' => (int)$last['id'], 'anime_title' => $last['title'],
                'episode_id' => $ep ? (int)$ep['id'] : null, 'episode_number' => $ep_num,
                'confidence' => $ep ? 0.85 : 0.5
            ];
        }
    }

    // Strategy 4: "Episode 12" or "Ep 12" without title — same context inference
    if (preg_match('/(?:ep|episode|#)\s*(\d+)/i', $caption, $m)) {
        $ep_num = (int)$m[1];
        $last = DB::fetch(
            "SELECT a.id, a.title FROM anime a
             INNER JOIN episodes e ON e.anime_id = a.id
             INNER JOIN telegram_videos tv ON tv.assigned_to_episode_id = e.id
             ORDER BY tv.created_at DESC LIMIT 1"
        );
        if ($last) {
            $ep = DB::fetch("SELECT id FROM episodes WHERE anime_id = ? AND number = ?", [$last['id'], $ep_num]);
            return [
                'anime_id' => (int)$last['id'], 'anime_title' => $last['title'],
                'episode_id' => $ep ? (int)$ep['id'] : null, 'episode_number' => $ep_num,
                'confidence' => $ep ? 0.6 : 0.3
            ];
        }
    }

    return null;
}

function tme_url_from_video(array $vid): string {
    if ($vid['chat_id'] && $vid['message_id']) {
        $chat = ltrim($vid['chat_id'], '-');
        if (str_starts_with($vid['chat_id'], '-100')) {
            return 'https://t.me/c/' . $chat . '/' . $vid['message_id'];
        }
        return 'https://t.me/c/' . $chat . '/' . $vid['message_id'];
    }
    return '';
}

function find_admin_chat(): ?string {
    $admin = DB::fetch("SELECT chat_id FROM telegram_subscribers WHERE active = 1 ORDER BY created_at ASC LIMIT 1");
    return $admin ? $admin['chat_id'] : null;
}

function notify_admin_about_video(TelegramBot $bot, int $video_id, string $caption, ?array $match): void {
    $admin_chat = find_admin_chat();
    if (!$admin_chat) return;

    $vid = DB::fetch("SELECT * FROM telegram_videos WHERE id = ?", [$video_id]);
    if (!$vid) return;

    $name = $vid['file_name'] ?: 'video.mp4';
    $dur = $vid['duration'] ? gmdate('i:s', $vid['duration']) : '?';
    $size = $vid['file_size'] ? round($vid['file_size'] / 1024 / 1024, 1) . ' MB' : '?';
    $tme_link = tme_url_from_video($vid);

    $msg = "📹 <b>New Channel Video</b>\n\n"
         . "File: <code>{$name}</code>\n"
         . "Size: {$size}  Duration: {$dur}s\n"
         . "Caption: " . htmlspecialchars($caption ?: '(none)') . "\n"
         . ($tme_link ? "🔗 <a href=\"{$tme_link}\">Open in Telegram</a>\n" : "");

    $keyboard = [];

    if ($match && $match['confidence'] >= 0.4) {
        $anime_link = BASE_URL . '/admin/anime.php?action=edit&id=' . $match['anime_id'];
        $msg .= "\n🔍 <b>Match found:</b> {$match['anime_title']} — Ep #{$match['episode_number']} (confidence: " . round($match['confidence'] * 100) . "%)";

        if ($match['episode_id']) {
            $keyboard[] = [
                ['text' => '✅ Attach to Episode', 'callback_data' => json_encode(['a' => 'attach', 'v' => $video_id, 'e' => $match['episode_id']])]
            ];
        } else {
            $msg .= "\n⚠️ Episode #{$match['episode_number']} doesn't exist yet. Create it first in the admin panel.";
            $keyboard[] = [
                ['text' => '📝 Create Episode', 'url' => BASE_URL . '/admin/episodes.php?action=create&anime_id=' . $match['anime_id']]
            ];
        }
    } else {
        $msg .= "\n❓ Could not match to any anime. You can attach manually in the admin panel.";
    }

    $keyboard[] = [
        ['text' => '🗑️ Ignore & Delete', 'callback_data' => json_encode(['a' => 'ignore', 'v' => $video_id])],
        ['text' => '📋 Admin Panel', 'url' => BASE_URL . '/admin/telegram-videos.php']
    ];

    $bot->sendMessage($admin_chat, $msg, 'HTML', ['reply_markup' => ['inline_keyboard' => $keyboard]]);
}

function attach_video_to_episode(int $video_id, int $episode_id, TelegramBot $bot, string $callback_query_id, string $chat_id, int $msg_id): void {
    $vid = DB::fetch("SELECT * FROM telegram_videos WHERE id = ?", [$video_id]);
    $ep = DB::fetch("SELECT e.id, e.number, a.title as anime_title FROM episodes e INNER JOIN anime a ON a.id = e.anime_id WHERE e.id = ?", [$episode_id]);
    if (!$vid || !$ep || $vid['assigned_to_episode_id']) {
        $bot->call('answerCallbackQuery', ['callback_query_id' => $callback_query_id, 'text' => 'Cannot attach — already assigned or missing.', 'show_alert' => true]);
        return;
    }
    $tme_link = tme_url_from_video($vid);
    $source_url = $tme_link ?: (BASE_URL . '/telegram-proxy.php?fid=' . urlencode($vid['file_id']));
    DB::insert(
        "INSERT INTO episode_sources (episode_id, language, source_type, label, url, quality) VALUES (?,?,?,?,?,?)",
        [$episode_id, 'sub', 'telegram', 'Telegram', $source_url, 'HD']
    );
    DB::execute("UPDATE telegram_videos SET assigned_to_episode_id = ? WHERE id = ?", [$episode_id, $video_id]);
    log_activity('Auto-attached Telegram video', 'episode_source', $episode_id, ['video_id' => $video_id, 'anime' => $ep['anime_title'], 'ep' => $ep['number']]);
    $bot->call('answerCallbackQuery', ['callback_query_id' => $callback_query_id, 'text' => "✅ Attached to {$ep['anime_title']} #{$ep['number']}!"]);
    $bot->call('editMessageText', [
        'chat_id' => $chat_id, 'message_id' => $msg_id,
        'text' => "✅ <b>Attached!</b>\n{$ep['anime_title']} — Episode #{$ep['number']}\n" . ($tme_link ? "🔗 <a href=\"{$tme_link}\">Open in Telegram</a>" : ""),
        'parse_mode' => 'HTML',
        'reply_markup' => ['inline_keyboard' => []]
    ]);
}

function ignore_video(int $video_id, TelegramBot $bot, string $callback_query_id, string $chat_id, int $msg_id): void {
    DB::execute("DELETE FROM telegram_videos WHERE id = ? AND assigned_to_episode_id IS NULL", [$video_id]);
    $bot->call('answerCallbackQuery', ['callback_query_id' => $callback_query_id, 'text' => '🗑️ Deleted.']);
    $bot->call('editMessageText', [
        'chat_id' => $chat_id, 'message_id' => $msg_id,
        'text' => "🗑️ Video ignored and removed.",
        'reply_markup' => ['inline_keyboard' => []]
    ]);
}

// ─── Callback Query (admin responds to Attach / Ignore) ─────────────────────

$callback_query = $update['callback_query'] ?? null;
if ($callback_query) {
    $data = json_decode($callback_query['data'] ?? '', true);
    $cq_id = $callback_query['id'];
    $cq_chat_id = $callback_query['message']['chat']['id'];
    $cq_msg_id = $callback_query['message']['message_id'];

    if (!$data || !isset($data['a'])) {
        $bot->call('answerCallbackQuery', ['callback_query_id' => $cq_id, 'text' => 'Invalid action.']);
        exit;
    }

    $action = $data['a'];
    $video_id = (int)($data['v'] ?? 0);

    if ($action === 'attach' && ($data['e'] ?? null)) {
        attach_video_to_episode($video_id, (int)$data['e'], $bot, $cq_id, $cq_chat_id, $cq_msg_id);
    } elseif ($action === 'ignore') {
        ignore_video($video_id, $bot, $cq_id, $cq_chat_id, $cq_msg_id);
    } else {
        $bot->call('answerCallbackQuery', ['callback_query_id' => $cq_id, 'text' => 'Unknown action.']);
    }
    exit;
}

// ─── Channel Post (new video posted in channel where bot is admin) ──────────

$channel_post = $update['channel_post'] ?? null;
if ($channel_post) {
    $channel_chat_id = $channel_post['chat']['id'] ?? '';
    $channel_username = $channel_post['chat']['username'] ?? '';
    $post_author_id = $channel_post['sender_chat']['id'] ?? $channel_chat_id;
    $post_author_name = $channel_post['sender_chat']['username'] ?? ($channel_username ? "@$channel_username" : 'channel');

    $video_id = save_video_from_message($channel_post, $channel_chat_id, $post_author_id, $post_author_name);
    if (!$video_id) exit;

    $caption = $channel_post['caption'] ?? '';
    $msg_id = $channel_post['message_id'] ?? 0;
    $tme_link = $channel_username ? "https://t.me/{$channel_username}/{$msg_id}" : '';
    if ($caption) {
        $match = match_anime_episode_from_caption($caption);
        if ($match && $match['confidence'] >= 0.8 && $match['episode_id']) {
            $source_url = $tme_link ?: (BASE_URL . '/telegram-proxy.php?fid=' . urlencode($channel_post['video']['file_id'] ?? $channel_post['document']['file_id'] ?? ''));
            DB::insert(
                "INSERT INTO episode_sources (episode_id, language, source_type, label, url, quality) VALUES (?,?,?,?,?,?)",
                [$match['episode_id'], 'sub', 'telegram', 'Telegram', $source_url, 'HD']
            );
            DB::execute("UPDATE telegram_videos SET assigned_to_episode_id = ? WHERE id = ?", [$match['episode_id'], $video_id]);
            log_activity('Auto-attached channel post', 'episode_source', $match['episode_id'], ['video_id' => $video_id, 'anime' => $match['anime_title'], 'ep' => $match['episode_number']]);
        } elseif ($match && $match['confidence'] >= 0.4) {
            notify_admin_about_video($bot, $video_id, $caption, $match);
        } else {
            notify_admin_about_video($bot, $video_id, $caption, null);
        }
    }
    exit;
}

// ─── Regular Message Handling ───────────────────────────────────────────────

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

$msg_data = $update['message'] ?? [];
$saved_id = save_video_from_message($msg_data, $chat_id, $update['message']['from']['id'] ?? '', $update['message']['from']['username'] ?? '');
if ($saved_id) {
    $vid = DB::fetch("SELECT file_id, file_name FROM telegram_videos WHERE id = ?", [$saved_id]);
    $fid = $vid['file_id'] ?? '';
    $fname = $vid['file_name'] ?? 'video.mp4';
    $bot->sendMessage($chat_id,
        "📹 <b>Video received!</b>\n\n"
        . "File: <code>{$fname}</code>\n"
        . "File ID: <code>{$fid}</code>\n\n"
        . "Stream URL:\n"
        . BASE_URL . "/telegram-proxy.php?file_id=" . urlencode($fid) . "\n\n"
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
            "SELECT id, file_name, file_id, created_at, assigned_to_episode_id FROM telegram_videos ORDER BY created_at DESC LIMIT 10"
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
            "SELECT e.title as ep_title, e.number, e.created_at, a.title, a.slug
             FROM episodes e INNER JOIN anime a ON a.id = e.anime_id
             ORDER BY e.created_at DESC LIMIT 5"
        );
        if (empty($eps)) { $bot->sendMessage($chat_id, "No episodes available yet."); break; }
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
        $results = DB::fetchAll("SELECT title, slug, type, episodes_count, rating FROM anime WHERE title LIKE ? LIMIT 5", ['%' . $search . '%']);
        if (empty($results)) {
            $bot->sendMessage($chat_id, "No results for \"" . htmlspecialchars($search) . "\".");
            break;
        }
        $msg = "<b>Search results for: " . htmlspecialchars($search) . "</b>\n\n";
        foreach ($results as $r) {
            $url = BASE_URL . '/' . $r['slug'];
            $msg .= "• <a href=\"{$url}\">{$r['title']}</a>" . ($r['type'] ? " [{$r['type']}]" : '') . ($r['rating'] ? " ⭐{$r['rating']}" : '') . "\n";
        }
        $bot->sendMessage($chat_id, $msg);
        break;

    default:
        $results = DB::fetchAll("SELECT title, slug, type FROM anime WHERE title LIKE ? LIMIT 3", ['%' . $text . '%']);
        if (!empty($results)) {
            $msg = "<b>Did you mean?</b>\n\n";
            foreach ($results as $r) {
                $msg .= "• <a href=\"" . BASE_URL . '/' . $r['slug'] . "\">{$r['title']}</a>\n";
            }
            $bot->sendMessage($chat_id, $msg);
        } else {
            $bot->sendMessage($chat_id, "Unknown command. Send /help to see available commands.");
        }
        break;
}
