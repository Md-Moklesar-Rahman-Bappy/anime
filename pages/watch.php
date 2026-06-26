<?php
$slug = $_GET['slug'] ?? '';
$anime = DB::fetch("SELECT * FROM anime WHERE slug = ?", [$slug]);
if (!$anime): http_response_code(404); echo '<h1>Anime not found</h1>'; return; endif;

$ep_num = max(1, (int)($_GET['ep'] ?? 1));
$episode = DB::fetch(
    "SELECT * FROM episodes WHERE anime_id = ? AND number = ?",
    [$anime['id'], $ep_num]
);

// If no exact episode match, get first episode
if (!$episode) {
    $episode = DB::fetch(
        "SELECT * FROM episodes WHERE anime_id = ? ORDER BY number ASC LIMIT 1",
        [$anime['id']]
    );
    $ep_num = $episode ? (int)$episode['number'] : 1;
}

// Get all episodes for this anime
$episodes = DB::fetchAll(
    "SELECT * FROM episodes WHERE anime_id = ? ORDER BY number ASC",
    [$anime['id']]
);

// Check next/prev
$ep_nums = array_column($episodes, 'number');
$current_idx = array_search($ep_num, $ep_nums);
$prev_ep = $current_idx > 0 ? $ep_nums[$current_idx - 1] : null;
$next_ep = $current_idx < count($ep_nums) - 1 ? $ep_nums[$current_idx + 1] : null;

// Get sources for this episode
$sources = [];
if ($episode) {
    $sources = DB::fetchAll(
        "SELECT * FROM episode_sources WHERE episode_id = ? ORDER BY language, id ASC",
        [$episode['id']]
    );
}

// Group sources by language
$sub_sources = array_filter($sources, fn($s) => $s['language'] === 'sub');
$dub_sources = array_filter($sources, fn($s) => $s['language'] === 'dub');
$current_lang = $_GET['lang'] ?? 'sub';
$current_sources = $current_lang === 'dub' && !empty($dub_sources) ? $dub_sources : $sub_sources;

// Update view counts
DB::execute("UPDATE anime SET views = views + 1 WHERE id = ?", [$anime['id']]);
if ($episode) {
    DB::execute("UPDATE episodes SET views = views + 1 WHERE id = ?", [$episode['id']]);
}

$thumb = $episode['thumbnail'] ?: ($anime['thumbnail'] ?: '');
$anime_title = escape($anime['title']);
$slug_esc = escape($slug);
$ep_title = $episode ? escape($episode['title'] ?: 'Episode ' . $ep_num) : 'Episode ' . $ep_num;
?>

<div class="watch-page">
    <div class="watch-container">
        <div class="player-section">
            <div class="player-wrapper" id="playerWrapper">
                <?php if (!empty($current_sources)): ?>
                    <?php
                    $first = reset($current_sources);
                    $st = $first['source_type'] ?? 'external';
                    $is_youtube = ($st === 'youtube');
                    $is_embed = (int)($first['embed'] ?? 0);
                    $has_iframe = !empty(trim($first['iframe_code'] ?? ''));
                    $is_hls = (int)($first['is_hls'] ?? 0);
                    ?>
                    <?php if ($is_youtube || ($is_embed && !$has_iframe && strpos($first['url'], 'youtube') !== false)): ?>
                        <?php
                        $yt_id = '';
                        if ($is_youtube && preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $first['url'], $m)) {
                            $yt_id = $m[1];
                        } elseif (preg_match('/^[a-zA-Z0-9_-]{11}$/', trim($first['url']))) {
                            $yt_id = trim($first['url']);
                        }
                        ?>
                        <?php if ($yt_id): ?>
                        <div class="player-embed">
                            <iframe src="https://www.youtube.com/embed/<?= escape($yt_id) ?>?autoplay=1&rel=0" frameborder="0" allowfullscreen allow="autoplay; fullscreen"></iframe>
                        </div>
                        <?php endif; ?>
                    <?php elseif ($has_iframe): ?>
                        <div class="player-embed"><?php
$safe_iframe = preg_replace('/<script[^>]*>.*?<\/script>/si', '', $first['iframe_code'] ?? '');
$safe_iframe = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $safe_iframe);
echo $safe_iframe;
?></div>
                    <?php elseif ($is_embed): ?>
                        <div class="player-embed">
                            <iframe src="<?= escape($first['url']) ?>" frameborder="0" allowfullscreen allow="autoplay; fullscreen"></iframe>
                        </div>
                    <?php elseif ($is_hls): ?>
                        <div class="player-embed" id="hlsPlayerContainer">
                            <video id="animePlayer" class="video-js vjs-default-skin" controls preload="auto" width="100%" height="100%"></video>
                        </div>
                        <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var video = document.getElementById('animePlayer');
                            var hlsUrl = '<?= escape($first['url']) ?>';
                            if (Hls.isSupported()) {
                                var hls = new Hls();
                                hls.loadSource(hlsUrl);
                                hls.attachMedia(video);
                                hls.on(Hls.Events.MANIFEST_PARSED, function() { video.play(); });
                            } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                                video.src = hlsUrl;
                                video.addEventListener('loadedmetadata', function() { video.play(); });
                            }
                        });
                        </script>
                    <?php else: ?>
                        <video id="animePlayer" controls preload="auto" width="100%" height="100%">
                            <?php foreach ($current_sources as $src): ?>
                                <source src="<?= escape($src['url']) ?>" type="video/mp4">
                            <?php endforeach; ?>
                        </video>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="player-placeholder">
                        <i class="fas fa-video"></i>
                        <p>No video source available</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Player Controls -->
            <div class="player-controls-bar">
                <div class="player-info">
                    <h2><?= $anime_title ?></h2>
                    <span class="ep-label">Episode <?= $ep_num ?></span>
                </div>
                <div class="player-actions">
                    <?php if (!empty($sub_sources) && !empty($dub_sources)): ?>
                    <div class="lang-toggle">
                        <a href="?slug=<?= $slug_esc ?>&ep=<?= $ep_num ?>&lang=sub" class="lang-btn-sm <?= $current_lang === 'sub' ? 'active' : '' ?>">SUB</a>
                        <a href="?slug=<?= $slug_esc ?>&ep=<?= $ep_num ?>&lang=dub" class="lang-btn-sm <?= $current_lang === 'dub' ? 'active' : '' ?>">DUB</a>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($current_sources) && count($current_sources) > 1): ?>
                    <div class="server-select">
                        <select id="serverSelect" class="server-dropdown">
                            <?php foreach ($current_sources as $i => $src): ?>
                                <option value="<?= $i ?>"><?= escape($src['label'] ?: 'Server #' . ($i + 1)) ?> (<?= escape($src['quality']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Episode Nav -->
            <div class="episode-nav">
                <?php if ($prev_ep): ?>
                    <a href="<?= url('watch/' . $slug_esc . '?ep=' . $prev_ep) ?>" class="btn btn-outline"><i class="fas fa-chevron-left"></i> Prev</a>
                <?php else: ?>
                    <span class="btn btn-outline disabled"><i class="fas fa-chevron-left"></i> Prev</span>
                <?php endif; ?>
                <span class="ep-nav-info">Episode <?= $ep_num ?> of <?= count($episodes) ?></span>
                <?php if ($next_ep): ?>
                    <a href="<?= url('watch/' . $slug_esc . '?ep=' . $next_ep) ?>" class="btn btn-primary">Next <i class="fas fa-chevron-right"></i></a>
                <?php else: ?>
                    <span class="btn btn-primary disabled">Next <i class="fas fa-chevron-right"></i></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar Episodes List -->
        <div class="watch-sidebar">
            <h3><i class="fas fa-list"></i> Episodes</h3>
            <div class="watch-episodes-list">
                <?php foreach ($episodes as $ep):
                    $is_active = (int)$ep['number'] === $ep_num;
                    $ep_title_item = escape($ep['title'] ?: 'Episode ' . $ep['number']);
                ?>
                <a href="<?= url('watch/' . $slug_esc . '?ep=' . $ep['number']) ?>" class="watch-ep-item <?= $is_active ? 'active' : '' ?>">
                    <span class="watch-ep-num"><?= $ep['number'] ?></span>
                    <div class="watch-ep-info">
                        <span class="watch-ep-title"><?= $ep_title_item ?></span>
                    </div>
                    <?php if ($ep['has_dub']): ?><span class="badge lang-badge dub-badge mini">DUB</span><?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const serverSelect = document.getElementById('serverSelect');
    if (serverSelect) {
        serverSelect.addEventListener('change', function() {
            const sources = <?= json_encode(array_values($current_sources)) ?>;
            const idx = parseInt(this.value);
            const src = sources[idx];
            const wrapper = document.getElementById('playerWrapper');
            if (!wrapper || !src) return;
            var isYt = src.source_type === 'youtube' || (src.url && src.url.indexOf('youtube') !== -1);
            var isEmbed = parseInt(src.embed) === 1;
            var hasIframe = src.iframe_code && src.iframe_code.trim().length > 0;
            var isHls = parseInt(src.is_hls) === 1;
            var ytId = '';
            if (isYt) {
                var m = src.url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
                if (m) ytId = m[1];
                else if (src.url.match(/^[a-zA-Z0-9_-]{11}$/)) ytId = src.url;
            }
            if (isYt && ytId) {
                wrapper.innerHTML = '<div class="player-embed"><iframe src="https://www.youtube.com/embed/' + ytId + '?autoplay=1&rel=0" frameborder="0" allowfullscreen allow="autoplay; fullscreen"></iframe></div>';
            } else if (hasIframe) {
                wrapper.innerHTML = '<div class="player-embed">' + src.iframe_code + '</div>';
            } else if (isEmbed) {
                wrapper.innerHTML = '<div class="player-embed"><iframe src="' + src.url + '" frameborder="0" allowfullscreen allow="autoplay; fullscreen"></iframe></div>';
            } else if (isHls) {
                wrapper.innerHTML = '<div class="player-embed" id="hlsPlayerContainer"><video id="animePlayer" class="video-js vjs-default-skin" controls preload="auto" width="100%" height="100%"></video></div>';
                var video = document.getElementById('animePlayer');
                if (typeof Hls !== 'undefined' && Hls.isSupported()) {
                    var hls = new Hls();
                    hls.loadSource(src.url);
                    hls.attachMedia(video);
                    hls.on(Hls.Events.MANIFEST_PARSED, function() { video.play(); });
                } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                    video.src = src.url;
                    video.addEventListener('loadedmetadata', function() { video.play(); });
                }
            } else {
                wrapper.innerHTML = '<video id="animePlayer" controls preload="auto" width="100%" height="100%"><source src="' + src.url + '" type="video/mp4"></video>';
                wrapper.querySelector('video').play();
            }
        });
    }
});
</script>
