<?php
require_once __DIR__ . '/auth_check.php';
require_permission('episodes.create');
$page_title = 'Telegram Videos';
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/../includes/TelegramBot.php';

$bot = TelegramBot::getInstance();

// Create table if not exists
try {
    DB::query("CREATE TABLE IF NOT EXISTS `telegram_videos` (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `file_id` VARCHAR(255) NOT NULL,
        `file_unique_id` VARCHAR(255) DEFAULT NULL,
        `file_path` VARCHAR(512) DEFAULT NULL,
        `file_size` BIGINT UNSIGNED DEFAULT NULL,
        `file_name` VARCHAR(255) DEFAULT NULL,
        `duration` INT UNSIGNED DEFAULT NULL,
        `mime_type` VARCHAR(100) DEFAULT NULL,
        `width` INT UNSIGNED DEFAULT NULL,
        `height` INT UNSIGNED DEFAULT NULL,
        `thumbnail` VARCHAR(512) DEFAULT NULL,
        `caption` TEXT,
        `message_id` BIGINT UNSIGNED DEFAULT NULL,
        `chat_id` VARCHAR(100) DEFAULT NULL,
        `from_user_id` VARCHAR(100) DEFAULT NULL,
        `from_username` VARCHAR(255) DEFAULT NULL,
        `assigned_to_episode_id` INT UNSIGNED DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_file_unique_id` (`file_unique_id`),
        INDEX `idx_assigned` (`assigned_to_episode_id`),
        INDEX `idx_file_id` (`file_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Exception $e) {}

// Handle actions
$action = $_GET['action'] ?? '';

if ($action === 'attach' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $vid_id = (int)($_POST['video_id'] ?? 0);
    $episode_id = (int)($_POST['episode_id'] ?? 0);
    $language = $_POST['language'] ?? 'sub';
    $label = $_POST['label'] ?: 'Telegram';
    $quality = $_POST['quality'] ?? 'HD';

    if ($vid_id && $episode_id) {
        $vid = DB::fetch("SELECT * FROM telegram_videos WHERE id = ?", [$vid_id]);
        $ep = DB::fetch("SELECT id FROM episodes WHERE id = ?", [$episode_id]);
        if ($vid && $ep) {
            $proxy_url = BASE_URL . '/telegram-proxy.php?fid=' . urlencode($vid['file_id']);
            $existing = DB::fetch("SELECT id FROM episode_sources WHERE episode_id = ? AND source_type = 'telegram' AND url = ?", [$episode_id, $proxy_url]);
            if (!$existing) {
                DB::insert(
                    "INSERT INTO episode_sources (episode_id, language, source_type, label, url, quality) VALUES (?,?,?,?,?,?)",
                    [$episode_id, $language, 'telegram', $label, $proxy_url, $quality]
                );
                DB::execute("UPDATE telegram_videos SET assigned_to_episode_id = ? WHERE id = ?", [$episode_id, $vid_id]);
                log_activity('Attached Telegram video to episode', 'episode_source', 0, ['video_id' => $vid_id, 'episode_id' => $episode_id]);
                $_SESSION['admin_success'] = 'Video attached to episode #' . $episode_id;
            } else {
                $_SESSION['admin_info'] = 'This video is already attached to episode #' . $episode_id;
            }
        } else {
            $_SESSION['admin_error'] = 'Video or episode not found.';
        }
    } else {
        $_SESSION['admin_error'] = 'Missing video or episode selection.';
    }
    redirect(BASE_URL . '/admin/telegram-videos.php');
}

if ($action === 'delete_video') {
    $vid_id = (int)($_GET['id'] ?? 0);
    DB::execute("DELETE FROM telegram_videos WHERE id = ? AND assigned_to_episode_id IS NULL", [$vid_id]);
    $_SESSION['admin_success'] = 'Video removed.';
    redirect(BASE_URL . '/admin/telegram-videos.php');
}

if ($action === 'sync_channel') {
    $updates = $bot->getUpdates(0, 50, ['channel_post']);
    $found = 0;
    $max_offset = 0;
    foreach ($updates['result'] ?? [] as $update) {
        $uid = $update['update_id'];
        if ($uid > $max_offset) $max_offset = $uid;
        $channel_post = $update['channel_post'] ?? [];
        if (!$channel_post) continue;
        $video_data = $channel_post['video'] ?? $channel_post['document'] ?? null;
        $file_id = $video_data['file_id'] ?? null;
        if (!$video_data || !$file_id) continue;
        $file_name = $video_data['file_name'] ?? ($file_id . '.mp4');
        try {
            $existing = DB::fetch("SELECT id FROM telegram_videos WHERE file_id = ?", [$file_id]);
            if (!$existing) {
                $channel_chat_id = $channel_post['chat']['id'] ?? '';
                $channel_username = $channel_post['chat']['username'] ?? '';
                DB::insert(
                    "INSERT INTO telegram_videos (file_id, file_unique_id, file_size, file_name, duration, mime_type, width, height, thumbnail, caption, chat_id, from_user_id, from_username)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
                    [
                        $file_id, $video_data['file_unique_id'] ?? '', $video_data['file_size'] ?? 0,
                        $file_name, $video_data['duration'] ?? null, $video_data['mime_type'] ?? '',
                        $video_data['width'] ?? null, $video_data['height'] ?? null,
                        $video_data['thumbnail']['file_id'] ?? '', $channel_post['caption'] ?? '',
                        $channel_chat_id, 'channel', $channel_username ? "@$channel_username" : 'channel',
                    ]
                );
                $found++;
            }
        } catch (Exception $e) {}
    }
    if ($max_offset > 0) {
        $bot->getUpdates($max_offset + 1, 1);
    }
    log_activity('Sync channel', 'telegram', 0, ['found' => $found]);
    $_SESSION['admin_success'] = "Channel sync complete. Found {$found} new video(s).";
    redirect(BASE_URL . '/admin/telegram-videos.php');
}

if ($action === 'fetch_url' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $telegram_url = trim($_POST['telegram_url'] ?? '');
    if (preg_match('#t\.me/([a-z0-9_]+)/(\d+)#i', $telegram_url, $m)) {
        $channel = $m[1];
        $msg_id = (int)$m[2];

        // Check if the channel exists
        $chat_info = $bot->getChat('@' . $channel);
        if (!$chat_info || !isset($chat_info['result']['id'])) {
            $_SESSION['admin_error'] = 'Could not find channel @' . htmlspecialchars($channel) . '.';
            redirect(BASE_URL . '/admin/telegram-videos.php');
        }

        // Find an admin user's chat_id to forward the video to
        $target_chat = DB::fetch("SELECT chat_id FROM telegram_subscribers WHERE active = 1 ORDER BY created_at ASC LIMIT 1");

        if ($target_chat) {
            // Forward the message from the channel to the admin's DM
            $result = $bot->forwardMessage('@' . $channel, $msg_id, $target_chat['chat_id']);
            if ($result) {
                $_SESSION['admin_success'] = 'Video forwarded to your Telegram! The bot saved it. Check the list below and refresh the page.';
                log_activity('Forwarded Telegram video', 'telegram', 0, ['channel' => $channel, 'msg_id' => $msg_id, 'target' => $target_chat['chat_id']]);
            } else {
                // Fallback: try copyMessage
                $copy_result = $bot->copyMessage('@' . $channel, $msg_id, $target_chat['chat_id'], ['disable_notification' => true]);
                if ($copy_result) {
                    $_SESSION['admin_success'] = 'Video copied to your Telegram! The bot saved it. Check the list below.';
                } else {
                    $_SESSION['admin_error'] = 'Could not forward the message. Make sure @' . TELEGRAM_BOT_USERNAME . ' is an admin of @' . htmlspecialchars($channel) . '. You can also manually forward the video from the channel to the bot.';
                }
            }
        } else {
            $_SESSION['admin_error'] = 'No Telegram subscribers found. Send /start to @' . TELEGRAM_BOT_USERNAME . ' first, then try again.';
        }
    } else {
        $_SESSION['admin_error'] = 'Invalid Telegram URL. Use format: https://t.me/channelname/123';
    }
    redirect(BASE_URL . '/admin/telegram-videos.php');
}

// Determine filter
$filter = $_GET['filter'] ?? 'unassigned';
$where = '';
if ($filter === 'unassigned') $where = 'WHERE tv.assigned_to_episode_id IS NULL';
elseif ($filter === 'assigned') $where = 'WHERE tv.assigned_to_episode_id IS NOT NULL';

$videos = DB::fetchAll(
    "SELECT tv.*, e.number as ep_number, a.title as anime_title
     FROM telegram_videos tv
     LEFT JOIN episodes e ON e.id = tv.assigned_to_episode_id
     LEFT JOIN anime a ON a.id = e.anime_id
     $where
     ORDER BY tv.created_at DESC
     LIMIT 50"
);

$anime_list = DB::fetchAll("SELECT id, title FROM anime ORDER BY title");
$bot_info = $bot->getMe();
$bot_username = $bot_info['result']['username'] ?? TELEGRAM_BOT_USERNAME;
?>

<div class="stats-grid" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fab fa-telegram"></i></div>
        <div class="stat-info">
            <h3>@<?= htmlspecialchars($bot_username) ?></h3>
            <p>Bot</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-video"></i></div>
        <div class="stat-info">
            <h3><?= DB::fetch("SELECT COUNT(*) as cnt FROM telegram_videos WHERE assigned_to_episode_id IS NULL")['cnt'] ?? 0 ?></h3>
            <p>Unassigned</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <h3><?= DB::fetch("SELECT COUNT(*) as cnt FROM telegram_videos WHERE assigned_to_episode_id IS NOT NULL")['cnt'] ?? 0 ?></h3>
            <p>Assigned</p>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:20px;padding:16px;">
    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:12px;">
        <a href="?action=sync_channel" class="btn btn-info"><i class="fas fa-cloud-download-alt"></i> Sync Channel Posts</a>
        <span style="color:var(--text-muted);font-size:0.85rem;">
            <i class="fas fa-info-circle"></i> Also try forwarding a video from the channel directly to <strong>@<?= htmlspecialchars($bot_username) ?></strong>, or paste a <code>t.me/username/123</code> URL below.
        </span>
    </div>
    <form method="post" action="telegram-videos.php?action=fetch_url" style="display:flex;gap:8px;margin-top:10px;">
        <input type="url" name="telegram_url" class="form-control" placeholder="https://t.me/channelname/123" style="flex:1;" required>
        <button type="submit" class="btn btn-primary"><i class="fas fa-cloud-download-alt"></i> Fetch URL</button>
    </form>
</div>

<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <h3 class="card-title">Import Video from Telegram</h3>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <div>
            <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:8px;">📤 Forward to Bot</h4>
            <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:8px;">
                Open Telegram and forward a video to <strong>@<?= htmlspecialchars($bot_username) ?></strong>.
                The bot will save it and you can assign it here.
            </p>
        </div>
        <div>
            <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:8px;">🔗 Paste Channel Post URL</h4>
            <form method="post" action="telegram-videos.php?action=fetch_url">
                <div class="form-row" style="grid-template-columns:1fr auto;">
                    <input type="url" name="telegram_url" class="form-control" placeholder="https://t.me/aniwavebd/14" value="https://t.me/aniwavebd/14">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-download"></i> Fetch</button>
                </div>
                <p style="color:var(--text-muted);font-size:0.78rem;margin-top:4px;">Bot must be an admin of the channel for this to work. Otherwise, forward the video manually.</p>
            </form>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Videos (<?= count($videos) ?>)</h3>
        <div style="display:flex;gap:8px;">
            <a href="telegram-videos.php?filter=unassigned" class="btn btn-sm <?= $filter==='unassigned'?'btn-primary':'btn-secondary' ?>">Unassigned</a>
            <a href="telegram-videos.php?filter=assigned" class="btn btn-sm <?= $filter==='assigned'?'btn-primary':'btn-secondary' ?>">Assigned</a>
            <a href="telegram-videos.php" class="btn btn-sm <?= $filter==='all'||$filter===''?'btn-primary':'btn-secondary' ?>">All</a>
        </div>
    </div>
    <?php if (count($videos) > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>File</th>
                    <th>Size</th>
                    <th>Info</th>
                    <th>From</th>
                    <th>Assigned To</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($videos as $v):
                    $size = $v['file_size'] ? round($v['file_size'] / 1024 / 1024, 1) . ' MB' : '-';
                    $dims = ($v['width'] && $v['height']) ? "{$v['width']}x{$v['height']}" : '-';
                    $dur = $v['duration'] ? gmdate('i:s', $v['duration']) : '-';
                    $proxy_url = BASE_URL . '/telegram-proxy.php?fid=' . urlencode($v['file_id']);
                ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($v['file_name'] ?: 'video.mp4') ?></strong><br>
                        <code style="font-size:0.7rem;"><?= htmlspecialchars($v['file_id']) ?></code>
                    </td>
                    <td style="white-space:nowrap;"><?= $size ?></td>
                    <td style="white-space:nowrap;">
                        <span class="badge badge-purple"><?= $dims ?></span>
                        <span class="badge badge-blue"><?= $dur ?></span>
                    </td>
                    <td style="font-size:0.8rem;">
                        <?= htmlspecialchars($v['from_username'] ?: $v['chat_id'] ?: '-') ?>
                    </td>
                    <td>
                        <?php if ($v['assigned_to_episode_id']): ?>
                            <a href="episodes.php?action=edit&id=<?= $v['assigned_to_episode_id'] ?>">
                                <?= htmlspecialchars($v['anime_title'] ?? 'Episode') ?> #<?= $v['ep_number'] ?>
                            </a>
                        <?php else: ?>
                            <span class="badge badge-gray">Unassigned</span>
                        <?php endif; ?>
                    </td>
                    <td class="table-cell-actions" style="white-space:nowrap;">
                        <a href="<?= $proxy_url ?>" class="btn btn-sm btn-info" target="_blank" title="Stream"><i class="fas fa-play"></i></a>
                        <?php if (!$v['assigned_to_episode_id']): ?>
                        <button class="btn btn-sm btn-primary" onclick="openAttachModal(<?= $v['id'] ?>, '<?= htmlspecialchars($v['file_name'] ?: 'video.mp4', ENT_QUOTES) ?>')" title="Attach to Episode"><i class="fas fa-link"></i></button>
                        <a href="telegram-videos.php?action=delete_video&id=<?= $v['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remove this video?')"><i class="fas fa-trash"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fab fa-telegram"></i>
        <p>No videos yet. Forward a video to <strong>@<?= htmlspecialchars($bot_username) ?></strong> on Telegram.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Attach Modal -->
<div class="modal" id="attachModal">
    <div class="modal-content" style="max-width:500px;">
        <span class="modal-close" onclick="closeAttachModal()">&times;</span>
        <h3>Attach Video to Episode</h3>
        <form method="post" action="telegram-videos.php?action=attach">
            <input type="hidden" name="video_id" id="attachVideoId">
            <div class="form-group">
                <label>Video</label>
                <p id="attachVideoName" style="color:var(--text-muted);font-size:0.9rem;"></p>
            </div>
            <div class="form-group">
                <label>Search Anime</label>
                <input type="text" id="animeSearch" class="form-control" placeholder="Type anime name to search..." autocomplete="off">
                <div id="animeSearchResults" style="display:none;max-height:200px;overflow-y:auto;border:1px solid var(--border-color);border-radius:var(--radius-sm);margin-top:4px;background:var(--bg-input);"></div>
            </div>
            <div class="form-group">
                <label>Select Episode *</label>
                <select name="episode_id" id="attachEpisodeId" class="form-control" required>
                    <option value="">First search for an anime above...</option>
                </select>
            </div>
            <div class="form-row" style="grid-template-columns:1fr 1fr;">
                <div class="form-group">
                    <label>Language</label>
                    <select name="language" class="form-control">
                        <option value="sub">Sub</option>
                        <option value="dub">Dub</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quality</label>
                    <select name="quality" class="form-control">
                        <option value="HD">HD</option>
                        <option value="Full HD">Full HD</option>
                        <option value="SD">SD</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Server Label</label>
                <input type="text" name="label" class="form-control" value="Telegram">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-link"></i> Attach to Episode</button>
        </form>
    </div>
</div>

<script>
let searchTimer = null;
let selectedAnimeId = 0;

function openAttachModal(videoId, fileName) {
    document.getElementById('attachVideoId').value = videoId;
    document.getElementById('attachVideoName').textContent = fileName;
    document.getElementById('animeSearch').value = '';
    document.getElementById('animeSearchResults').style.display = 'none';
    document.getElementById('animeSearchResults').innerHTML = '';
    document.getElementById('attachEpisodeId').innerHTML = '<option value="">Search for an anime first...</option>';
    selectedAnimeId = 0;
    document.getElementById('attachModal').classList.add('show');
    setTimeout(function(){ document.getElementById('animeSearch').focus(); }, 100);
}

function closeAttachModal() {
    document.getElementById('attachModal').classList.remove('show');
}

document.getElementById('animeSearch').addEventListener('input', function() {
    clearTimeout(searchTimer);
    const q = this.value.trim();
    if (q.length < 1) {
        document.getElementById('animeSearchResults').style.display = 'none';
        document.getElementById('animeSearchResults').innerHTML = '';
        return;
    }
    searchTimer = setTimeout(function() {
        fetch(BASE_URL + '/ajax/search-anime.php?q=' + encodeURIComponent(q))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                const container = document.getElementById('animeSearchResults');
                if (!data || data.length === 0) {
                    container.innerHTML = '<div style="padding:8px;color:var(--text-muted);">No results.</div>';
                    container.style.display = 'block';
                    return;
                }
                var html = '';
                data.forEach(function(a) {
                    html += '<div class="anime-search-item" data-id="' + a.id + '" style="padding:8px 12px;cursor:pointer;border-bottom:1px solid var(--border-color);display:flex;align-items:center;gap:10px;" onmouseover="this.style.background=\'var(--bg-hover)\'" onmouseout="this.style.background=\'transparent\'" onclick="selectAnime(' + a.id + ')">';
                    if (a.thumbnail) html += '<img src="' + a.thumbnail + '" style="width:32px;height:44px;object-fit:cover;border-radius:2px;">';
                    else html += '<i class="fas fa-film" style="width:32px;text-align:center;color:var(--text-muted);"></i>';
                    html += '<div><strong>' + a.title + '</strong>';
                    if (a.type) html += ' <span style="color:var(--text-muted);font-size:0.78rem;">[' + a.type + ']</span>';
                    html += '</div></div>';
                });
                container.innerHTML = html;
                container.style.display = 'block';
            })
            .catch(function() {
                document.getElementById('animeSearchResults').innerHTML = '<div style="padding:8px;color:var(--danger);">Search failed.</div>';
            });
    }, 300);
});

function selectAnime(animeId) {
    selectedAnimeId = animeId;
    var items = document.querySelectorAll('.anime-search-item');
    items.forEach(function(item) {
        item.style.background = parseInt(item.dataset.id) === animeId ? 'var(--bg-hover)' : 'transparent';
    });
    document.getElementById('animeSearchResults').style.display = 'none';
    var epSelect = document.getElementById('attachEpisodeId');
    epSelect.innerHTML = '<option value="">Loading episodes...</option>';
    fetch(BASE_URL + '/ajax/search-anime.php?anime_id=' + animeId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.episodes && data.episodes.length > 0) {
                var html = '<option value="">Select episode...</option>';
                data.episodes.forEach(function(ep) {
                    var label = 'Episode #' + ep.number;
                    if (ep.title) label += ' - ' + ep.title;
                    html += '<option value="' + ep.id + '">' + label + '</option>';
                });
                epSelect.innerHTML = html;
            } else {
                epSelect.innerHTML = '<option value="">No episodes found for this anime</option>';
            }
        })
        .catch(function() {
            epSelect.innerHTML = '<option value="">Error loading episodes</option>';
        });
}
</script>

<style>
.anime-search-item:hover { background: var(--bg-hover) !important; }
.anime-search-item:last-child { border-bottom: none !important; }
</style>

<?php require_once __DIR__ . '/footer.php'; ?>
