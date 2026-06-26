<?php
$slug = $_GET['slug'] ?? '';
$anime = $_ANIME;
$episode_number = (int)($_GET['ep'] ?? 1);

$episode = DB::fetch("SELECT * FROM episodes WHERE anime_id = ? AND number = ?", [$anime['id'], $episode_number]);
if (!$episode) {
    $episode = DB::fetch("SELECT * FROM episodes WHERE anime_id = ? ORDER BY number ASC LIMIT 1", [$anime['id']]);
    if ($episode) $episode_number = $episode['number'];
}

// Get all episodes
$episodes = DB::fetchAll("SELECT * FROM episodes WHERE anime_id = ? ORDER BY number ASC", [$anime['id']]);

// Get sources
$sources = [];
if ($episode) {
    $sources = DB::fetchAll("SELECT * FROM episode_sources WHERE episode_id = ? ORDER BY id ASC", [$episode['id']]);
}

// Get skip times
$skip_times = [];
if ($episode) {
    $skip_times = DB::fetchAll("SELECT type, start, `end` FROM skip_times WHERE episode_id = ? ORDER BY start ASC", [$episode['id']]);
}

// Prev/Next
$prev_ep = null;
$next_ep = null;
foreach ($episodes as $i => $ep) {
    if ($ep['number'] == $episode_number) {
        if ($i > 0) $prev_ep = $episodes[$i - 1];
        if ($i < count($episodes) - 1) $next_ep = $episodes[$i + 1];
        break;
    }
}

// View count (with session guard)
if ($episode && !isset($_SESSION["viewed_ep_{$episode['id']}"])) {
    DB::execute("UPDATE episodes SET views = views + 1 WHERE id = ?", [$episode['id']]);
    DB::execute("UPDATE anime SET views = views + 1 WHERE id = ?", [$anime['id']]);
    $_SESSION["viewed_ep_{$episode['id']}"] = true;
}

$ep_title = $episode ? ($episode['title'] ?: "Episode {$episode_number}") : 'No episodes yet';
$pageTitle = $anime['title'] . ' - Episode ' . $episode_number . ' - ' . SITE_NAME;

// OG meta
$ogTitle = $pageTitle;
$ogDesc = truncate(strip_tags($anime['description'] ?? ''), 200);
$ogImage = $anime['thumbnail'] ?: $anime['banner'];
$ogType = 'video.episode';
$pageDesc = "Watch {$anime['title']} Episode {$episode_number} online.";

$user_id = $_SESSION['user_id'] ?? 0;

// Get user progress
$progress = 0;
if ($episode && $user_id) {
    $wh = DB::fetch("SELECT progress FROM watch_history WHERE user_id = ? AND episode_id = ?", [$user_id, $episode['id']]);
    $progress = $wh ? (int)$wh['progress'] : 0;
}

// Get comments
$comments = [];
if ($episode) {
    $comments = DB::fetchAll(
        "SELECT c.*, u.username, u.avatar FROM comments c
         LEFT JOIN users u ON u.id = c.user_id
         WHERE c.episode_id = ? ORDER BY c.created_at DESC LIMIT 100",
        [$episode['id']]
    );
}

// Get user's favorite status for this anime
$fav_status = null;
if ($user_id) {
    $fav = DB::fetch("SELECT list_type FROM favorites WHERE user_id = ? AND anime_id = ?", [$user_id, $anime['id']]);
    $fav_status = $fav ? $fav['list_type'] : null;
}
?>
<link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css">
<script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

<div class="watch-page">
    <div class="watch-main">
        <!-- Player -->
        <div class="player-wrapper" id="playerWrapper">
            <div class="player-container" id="playerContainer">
                <?php if ($episode && !empty($sources)): ?>
                <video id="player" playsinline controls data-poster="<?= escape($episode['thumbnail'] ?: $anime['thumbnail']) ?>">
                    <?php foreach ($sources as $src):
                        if ($src['source_type'] === 'youtube' && $src['url']): ?>
                            <source src="<?= escape($src['url']) ?>" type="video/youtube" data-label="<?= escape($src['label'] ?: 'Server') ?>" data-quality="<?= escape($src['quality'] ?: 'HD') ?>" data-lang="<?= escape($src['language'] ?: 'sub') ?>">
                        <?php elseif ($src['source_type'] === 'external' && $src['iframe_code']): ?>
                            <!-- iframe source: <?= escape($src['label']) ?> -->
                        <?php elseif ($src['url']): ?>
                            <source src="<?= escape($src['url']) ?>" data-label="<?= escape($src['label'] ?: 'Server') ?>" data-quality="<?= escape($src['quality'] ?: 'HD') ?>" data-lang="<?= escape($src['language'] ?: 'sub') ?>" <?= $src['is_hls'] ? 'data-hls' : '' ?>>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </video>
                <?php elseif ($episode): ?>
                    <?php
                    // Check for iframe sources
                    $iframe_src = null;
                    foreach ($sources as $src) {
                        if ($src['source_type'] === 'external' && $src['iframe_code']) {
                            $iframe_src = $src;
                            break;
                        }
                        if ($src['source_type'] === 'youtube' && $src['iframe_code']) {
                            $iframe_src = $src;
                            break;
                        }
                    }
                    if ($iframe_src): ?>
                    <div class="iframe-player">
                        <?= $iframe_src['iframe_code'] ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state"><i class="fas fa-video-slash"></i><p>No video sources available for this episode.</p></div>
                    <?php endif; ?>
                <?php else: ?>
                <div class="empty-state"><i class="fas fa-film"></i><p>No episodes available yet. Check back soon!</p></div>
                <?php endif; ?>
            </div>
            <!-- Server selector -->
            <?php if ($episode && count($sources) > 0): ?>
            <div class="server-selector">
                <div class="server-tabs">
                    <?php
                    $langs = ['sub' => 'Sub', 'dub' => 'Dub'];
                    $has_lang = [];
                    foreach ($sources as $src) { $has_lang[$src['language']] = true; }
                    foreach ($langs as $key => $label):
                        if (!isset($has_lang[$key])) continue;
                    ?>
                    <button class="server-lang-btn <?= $key === 'sub' ? 'active' : '' ?>" data-lang="<?= $key ?>"><?= $label ?></button>
                    <?php endforeach; ?>
                </div>
                <div class="server-list" id="serverList">
                    <?php foreach ($sources as $i => $src): ?>
                    <button class="server-btn <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>" data-lang="<?= escape($src['language'] ?: 'sub') ?>" data-url="<?= escape($src['url']) ?>" data-type="<?= escape($src['source_type']) ?>" data-hls="<?= $src['is_hls'] ?>" data-iframe="<?= escape($src['iframe_code']) ?>" data-label="<?= escape($src['label'] ?: 'Server') ?>" data-quality="<?= escape($src['quality'] ?: 'HD') ?>">
                        <span class="server-name"><?= escape($src['label'] ?: 'Server') ?></span>
                        <span class="server-quality"><?= escape($src['quality'] ?: 'HD') ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <!-- Episode info bar -->
            <div class="episode-info-bar">
                <div class="ep-info-left">
                    <h2><a href="<?= url($anime['slug']) ?>"><?= escape($anime['title']) ?></a> - Episode <?= $episode_number ?></h2>
                    <?php if ($episode && $episode['title']): ?><span class="ep-subtitle"><?= escape($episode['title']) ?></span><?php endif; ?>
                </div>
                <div class="ep-info-right">
                    <?php if ($prev_ep): ?>
                    <a href="<?= url('watch/' . $anime['slug'] . '?ep=' . $prev_ep['number']) ?>" class="btn btn-sm btn-outline"><i class="fas fa-step-backward"></i> Prev</a>
                    <?php endif; ?>
                    <div class="ep-dropdown">
                        <button class="btn btn-sm btn-outline" onclick="this.nextElementSibling.classList.toggle('show')"><i class="fas fa-list"></i> Episodes</button>
                        <div class="ep-dropdown-menu">
                            <?php foreach ($episodes as $ep): ?>
                            <a href="<?= url('watch/' . $anime['slug'] . '?ep=' . $ep['number']) ?>" class="ep-dropdown-item <?= $ep['number'] == $episode_number ? 'active' : '' ?>">
                                <?= $ep['number'] ?>. <?= escape($ep['title'] ?: "Episode {$ep['number']}") ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php if ($next_ep): ?>
                    <a href="<?= url('watch/' . $anime['slug'] . '?ep=' . $next_ep['number']) ?>" class="btn btn-sm btn-primary"><i class="fas fa-step-forward"></i> Next</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Comments section -->
        <div class="comments-section">
            <h3><i class="fas fa-comments"></i> Comments (<?= count($comments) ?>)</h3>
            <?php if ($user_id): ?>
            <div class="comment-form">
                <textarea id="commentBody" rows="2" placeholder="Share your thoughts..." maxlength="2000"></textarea>
                <button onclick="postComment(<?= $episode['id'] ?? 0 ?>, <?= $anime['id'] ?>)" class="btn btn-primary btn-sm"><i class="fas fa-paper-plane"></i> Post</button>
            </div>
            <?php else: ?>
            <p style="color:var(--text-muted);font-size:0.85rem;"><a href="#" onclick="openModal('loginModal')">Log in</a> to comment.</p>
            <?php endif; ?>
            <div class="comments-list" id="commentsList">
                <?php foreach ($comments as $c): ?>
                <div class="comment-item" data-id="<?= $c['id'] ?>">
                    <img src="<?= escape($c['avatar'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($c['username']) . '&background=6c5ce7&color=fff&size=40') ?>" alt="" class="comment-avatar">
                    <div class="comment-body">
                        <div class="comment-header">
                            <strong><?= escape($c['username']) ?></strong>
                            <span class="comment-time"><?= time_ago($c['created_at']) ?></span>
                            <?php if ($user_id && ($c['user_id'] == $user_id || is_admin())): ?>
                            <button onclick="deleteComment(<?= $c['id'] ?>)" class="comment-delete"><i class="fas fa-times"></i></button>
                            <?php endif; ?>
                        </div>
                        <p><?= nl2br(escape($c['body'])) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="watch-sidebar">
        <!-- Favorite/My List -->
        <div class="watch-actions">
            <button class="btn btn-sm <?= $fav_status ? 'btn-primary' : 'btn-outline' ?>" id="favBtn" onclick="toggleFavorite(<?= $anime['id'] ?>)">
                <i class="fas fa-bookmark"></i> <span id="favLabel"><?= $fav_status ? str_replace('_', ' ', ucwords($fav_status)) : 'Add to List' ?></span>
            </button>
            <button class="btn btn-sm btn-outline" onclick="openReportModal(<?= $episode['id'] ?? 0 ?>, <?= $anime['id'] ?>)">
                <i class="fas fa-flag"></i> Report
            </button>
        </div>

        <!-- Episode list -->
        <div class="watch-ep-list">
            <h4><i class="fas fa-list"></i> Episodes</h4>
            <div class="ep-list-scroll">
                <?php foreach ($episodes as $ep): ?>
                <a href="<?= url('watch/' . $anime['slug'] . '?ep=' . $ep['number']) ?>" class="ep-list-item <?= $ep['number'] == $episode_number ? 'active' : '' ?>">
                    <span class="ep-num"><?= $ep['number'] ?></span>
                    <span class="ep-title"><?= escape($ep['title'] ?: "Episode {$ep['number']}") ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Report Modal -->
<div class="modal" id="reportModal">
    <div class="modal-content" style="max-width:400px;">
        <span class="modal-close" onclick="closeModal('reportModal')">&times;</span>
        <h3>Report Issue</h3>
        <form id="reportForm" onsubmit="submitReport(event)">
            <input type="hidden" name="episode_id" id="reportEpisodeId">
            <input type="hidden" name="anime_id" id="reportAnimeId">
            <div class="form-group">
                <label>Issue Type</label>
                <select name="type" class="form-control" id="reportType">
                    <option value="broken_video">Broken Video</option>
                    <option value="wrong_episode">Wrong Episode</option>
                    <option value="subtitle_issue">Subtitle Issue</option>
                    <option value="audio_issue">Audio Issue</option>
                    <option value="wrong_source">Wrong Source</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Description (optional)</label>
                <textarea name="description" class="form-control" rows="2" id="reportDesc"></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Submit Report</button>
        </form>
    </div>
</div>

<script>
// ---- Plyr + HLS Setup ----
document.addEventListener('DOMContentLoaded', function() {
    const playerEl = document.getElementById('player');
    if (!playerEl) return;

    // Detect HLS sources and use hls.js
    const sources = playerEl.querySelectorAll('source[data-hls]');
    let hls = null;

    if (sources.length > 0 && Hls.isSupported()) {
        const hlsUrl = sources[0].getAttribute('src');
        hls = new Hls({
            enableWorker: true,
            lowLatencyMode: true,
        });
        hls.loadSource(hlsUrl);
        hls.attachMedia(playerEl);
        hls.on(Hls.Events.MANIFEST_PARSED, function() {
            player.play();
        });
    }

    const player = new Plyr(playerEl, {
        controls: [
            'play-large', 'play', 'progress', 'current-time', 'duration',
            'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen'
        ],
        settings: ['captions', 'quality', 'speed', 'loop'],
        keyboard: { focused: true, global: true },
        tooltips: { controls: true, seek: true },
        seekTime: 10,
        speed: { selected: 1, options: [0.5, 0.75, 1, 1.25, 1.5, 1.75, 2] },
        quality: {
            default: 576,
            options: [4320, 2880, 2160, 1440, 1080, 720, 576, 480, 360, 240],
        },
        resetOnEnd: true,
        disableContextMenu: true,
        ratio: '16:9',
    });

    // Save progress every 15 seconds
    const epId = <?= $episode['id'] ?? 0 ?>;
    if (epId) {
        player.on('timeupdate', function() {
            const duration = player.duration;
            if (duration > 0) {
                const pct = Math.round((player.currentTime / duration) * 100);
                if (pct % 5 === 0 || pct >= 90) {
                    saveProgress(epId, pct);
                }
            }
        });

        // Restore progress
        <?php if ($progress > 0 && $progress < 90): ?>
        player.on('ready', function() {
            const dur = player.duration;
            if (dur > 0) {
                player.currentTime = (<?= $progress ?> / 100) * dur;
            }
        });
        <?php endif; ?>
    }

    // ---- Skip intro/outro buttons ----
    const skipTimes = <?= json_encode($skip_times) ?>;
    let skipInterval = null;

    function checkSkip() {
        if (!player || !player.playing) return;
        const ct = player.currentTime;
        for (const s of skipTimes) {
            if (ct >= s.start && ct < s.end) {
                player.currentTime = parseFloat(s.end);
                showSkipNotification(s.type);
                break;
            }
        }
    }

    function showSkipNotification(type) {
        const label = type === 'intro' ? 'Intro' : 'Outro';
        const el = document.createElement('div');
        el.className = 'skip-notification';
        el.textContent = `Skipped ${label}`;
        document.getElementById('playerContainer')?.appendChild(el);
        setTimeout(() => el.remove(), 2000);
    }

    if (skipTimes.length > 0) {
        player.on('playing', function() {
            skipInterval = setInterval(checkSkip, 500);
        });
        player.on('pause', function() {
            if (skipInterval) clearInterval(skipInterval);
        });
        player.on('ended', function() {
            if (skipInterval) clearInterval(skipInterval);
        });
    }

    // Auto-next episode
    player.on('ended', function() {
        const nextLink = document.querySelector('.ep-info-right .btn-primary[href*="ep="]');
        if (nextLink) {
            setTimeout(() => { window.location.href = nextLink.href; }, 3000);
        }
    });

    // ---- Quality selector refresh for HLS ----
    if (hls) {
        hls.on(Hls.Events.LEVEL_SWITCHED, function(event, data) {
            const levels = hls.levels;
            if (levels && levels[data.level]) {
                player.quality = levels[data.level].height;
            }
        });
    }
});

// ---- Server switching ----
document.querySelectorAll('.server-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.server-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const url = this.dataset.url;
        const type = this.dataset.type;
        const isHls = this.dataset.hls === '1';
        const iframeCode = this.dataset.iframe;
        const playerEl = document.getElementById('player');
        const container = document.getElementById('playerContainer');

        if (type === 'youtube' && url) {
            // For YouTube, destroy Plyr and load iframe
            const playerEl = document.getElementById('player');
            if (playerEl) {
                const plyrInst = playerEl.plyr;
                if (plyrInst) plyrInst.destroy();
            }
            container.innerHTML = `<div class="iframe-player"><iframe width="100%" height="100%" src="${url}" frameborder="0" allowfullscreen></iframe></div>`;
        } else if (iframeCode) {
            container.innerHTML = `<div class="iframe-player">${iframeCode}</div>`;
        } else if (url) {
            // Re-create video element
            container.innerHTML = `<video id="player" playsinline controls></video>`;
            const newPlayer = document.getElementById('player');
            if (isHls && Hls.isSupported()) {
                const hls = new Hls();
                hls.loadSource(url);
                hls.attachMedia(newPlayer);
            } else {
                newPlayer.innerHTML = `<source src="${url}">`;
            }
            new Plyr(newPlayer, {
                controls: ['play-large', 'play', 'progress', 'current-time', 'duration', 'mute', 'volume', 'settings', 'pip', 'fullscreen'],
                keyboard: { focused: true, global: true },
                seekTime: 10,
                speed: { selected: 1, options: [0.5, 0.75, 1, 1.25, 1.5, 1.75, 2] },
            });
        }
    });
});

// Language tabs
document.querySelectorAll('.server-lang-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.server-lang-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const lang = this.dataset.lang;
        document.querySelectorAll('.server-btn').forEach(function(s) {
            s.style.display = s.dataset.lang === lang ? 'flex' : 'none';
        });
        // Activate first visible
        const visible = document.querySelector('.server-btn:not([style*="display: none"])');
        if (visible) visible.click();
    });
});
// Init language filter
(function() {
    const firstLang = document.querySelector('.server-lang-btn.active')?.dataset.lang;
    if (firstLang) {
        document.querySelectorAll('.server-btn').forEach(function(s) {
            if (s.dataset.lang !== firstLang) s.style.display = 'none';
        });
    }
})();

// ---- Comments ----
function postComment(episodeId, animeId) {
    const body = document.getElementById('commentBody');
    if (!body.value.trim()) return;
    fetch(BASE_URL + '/ajax/comment', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=post&episode_id=' + episodeId + '&anime_id=' + animeId + '&body=' + encodeURIComponent(body.value)
    }).then(r => r.json()).then(data => {
        if (data.status === 'ok') {
            const list = document.getElementById('commentsList');
            const div = document.createElement('div');
            div.className = 'comment-item';
            div.dataset.id = data.id;
            div.innerHTML = `<img src="${data.avatar}" alt="" class="comment-avatar"><div class="comment-body"><div class="comment-header"><strong>${data.username}</strong><span class="comment-time">${data.created_at}</span><button onclick="deleteComment(${data.id})" class="comment-delete"><i class="fas fa-times"></i></button></div><p>${data.body}</p></div>`;
            list.prepend(div);
            body.value = '';
        } else {
            alert(data.error || 'Error posting comment');
        }
    });
}

function deleteComment(id) {
    if (!confirm('Delete this comment?')) return;
    fetch(BASE_URL + '/ajax/comment', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=delete&id=' + id
    }).then(r => r.json()).then(data => {
        if (data.status === 'deleted') {
            document.querySelector(`.comment-item[data-id="${id}"]`)?.remove();
        }
    });
}

// Poll for new comments every 10s
<?php if ($episode): ?>
setInterval(function() {
    fetch(BASE_URL + '/ajax/comment?action=list&episode_id=<?= $episode['id'] ?>')
    .then(r => r.json()).then(comments => {
        const list = document.getElementById('commentsList');
        if (!list) return;
        // Only update if there are new comments
        const existing = list.querySelectorAll('.comment-item');
        if (comments.length > existing.length) {
            const existingIds = new Set();
            existing.forEach(e => existingIds.add(parseInt(e.dataset.id)));
            for (const c of comments) {
                if (!existingIds.has(c.id)) {
                    const div = document.createElement('div');
                    div.className = 'comment-item';
                    div.dataset.id = c.id;
                    const avatar = c.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(c.username) + '&background=6c5ce7&color=fff&size=40';
                    div.innerHTML = `<img src="${avatar}" alt="" class="comment-avatar"><div class="comment-body"><div class="comment-header"><strong>${c.username}</strong><span class="comment-time">Just now</span></div><p>${c.body}</p></div>`;
                    list.prepend(div);
                }
            }
        }
    });
}, 10000);
<?php endif; ?>

// ---- Favorites ----
function toggleFavorite(animeId) {
    const btn = document.getElementById('favBtn');
    const label = document.getElementById('favLabel');
    const types = ['watching', 'completed', 'plan_to_watch', 'on_hold', 'dropped'];
    const current = label.textContent.trim().toLowerCase().replace(/ /g, '_');
    let next = 'watching';
    if (current !== 'add to list') {
        const idx = types.indexOf(current);
        next = idx >= 0 && idx < types.length - 1 ? types[idx + 1] : null;
    }

    if (next === null) {
        // Remove
        fetch(BASE_URL + '/ajax/favorite', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=remove&anime_id=' + animeId
        }).then(r => r.json()).then(data => {
            if (data.status === 'removed') {
                btn.className = 'btn btn-sm btn-outline';
                label.textContent = 'Add to List';
            }
        });
    } else {
        fetch(BASE_URL + '/ajax/favorite', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=toggle&anime_id=' + animeId + '&list_type=' + next
        }).then(r => r.json()).then(data => {
            if (data.status) {
                btn.className = 'btn btn-sm btn-primary';
                label.textContent = next.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            }
        });
    }
}

// ---- Report ----
function openReportModal(episodeId, animeId) {
    document.getElementById('reportEpisodeId').value = episodeId;
    document.getElementById('reportAnimeId').value = animeId;
    openModal('reportModal');
}

function submitReport(e) {
    e.preventDefault();
    const fd = new FormData(document.getElementById('reportForm'));
    fd.append('episode_id', document.getElementById('reportEpisodeId').value);
    fd.append('anime_id', document.getElementById('reportAnimeId').value);
    const params = new URLSearchParams(fd);
    fetch(BASE_URL + '/ajax/report', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: params.toString()
    }).then(r => r.json()).then(data => {
        if (data.status === 'ok') {
            alert(data.message);
            closeModal('reportModal');
        } else {
            alert(data.error || 'Error submitting report');
        }
    });
}

// ---- Progress saving ----
function saveProgress(episodeId, progress) {
    fetch(BASE_URL + '/ajax/watch-progress', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'episode_id=' + episodeId + '&progress=' + progress
    });
}
</script>
