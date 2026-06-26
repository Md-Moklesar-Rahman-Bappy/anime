<?php
$slug = $_GET['slug'] ?? '';
$anime = DB::fetch("SELECT * FROM anime WHERE slug = ?", [$slug]);
if (!$anime): http_response_code(404); echo '<h1>Anime not found</h1>'; return; endif;

// Update view count
DB::execute("UPDATE anime SET views = views + 1 WHERE id = ?", [$anime['id']]);

// OG meta
$ogTitle = $anime['title'];
$ogDesc = truncate(strip_tags($anime['description'] ?? ''), 200);
$ogImage = $anime['thumbnail'] ?: $anime['banner'];
$ogType = 'video.tv_show';
$pageDesc = $ogDesc;

$anime_genres = DB::fetchAll(
    "SELECT g.* FROM genres g JOIN anime_genre ag ON g.id = ag.genre_id WHERE ag.anime_id = ?", [$anime['id']]
);
$episodes = DB::fetchAll(
    "SELECT * FROM episodes WHERE anime_id = ? ORDER BY number ASC", [$anime['id']]
);
$total_ep = count($episodes);

$thumb = $anime['thumbnail'] ?: 'https://via.placeholder.com/300x450/1a1a2e/cccccc?text=No+Image';
$banner = $anime['banner'] ?: $thumb;
?>

<div class="anime-detail-page">
    <div class="detail-hero" style="background-image:linear-gradient(135deg, rgba(10,10,25,0.95) 0%, rgba(10,10,25,0.6) 50%, rgba(10,10,25,0.95) 100%),url('<?= escape($banner) ?>')">
        <div class="detail-hero-inner">
            <div class="detail-poster">
                <img src="<?= escape($thumb) ?>" alt="<?= escape($anime['title']) ?>">
            </div>
            <div class="detail-info">
                <div class="detail-badges">
                    <?= get_type_badge($anime['type']) ?>
                    <?= get_status_badge($anime['status']) ?>
                    <?php if ($anime['age_rating']): ?>
                    <span class="badge age-badge"><?= escape($anime['age_rating']) ?></span>
                    <?php endif; ?>
                </div>
                <h1 class="detail-title"><?= escape($anime['title']) ?></h1>
                <?php if ($anime['title_japanese']): ?>
                <p class="detail-jp"><?= escape($anime['title_japanese']) ?></p>
                <?php endif; ?>
                <div class="detail-stats">
                    <span><i class="fas fa-star"></i> <?= $anime['rating'] ?? '?' ?></span>
                    <span><i class="fas fa-eye"></i> <?= number_format($anime['views']) ?></span>
                    <span><i class="fas fa-film"></i> <?= $anime['type'] ?? 'N/A' ?></span>
                    <?php if ($anime['episodes_count'] > 0): ?>
                    <span><i class="fas fa-list"></i> <?= $anime['episodes_count'] ?> ep</span>
                    <?php endif; ?>
                    <?php if ($anime['duration']): ?>
                    <span><i class="far fa-clock"></i> <?= $anime['duration'] ?> min</span>
                    <?php endif; ?>
                    <?php if ($anime['year']): ?>
                    <span><i class="fas fa-calendar"></i> <?= $anime['year'] ?></span>
                    <?php endif; ?>
                    <?php if ($anime['season']): ?>
                    <span><i class="fas fa-leaf"></i> <?= $anime['season'] ?></span>
                    <?php endif; ?>
                </div>
                <div class="detail-desc">
                    <p><?= nl2br(escape($anime['description'])) ?></p>
                </div>
                <div class="detail-meta">
                    <?php if ($anime['studio']): ?>
                    <p><strong>Studio:</strong> <?= escape($anime['studio']) ?></p>
                    <?php endif; ?>
                    <?php if ($anime['source']): ?>
                    <p><strong>Source:</strong> <?= escape($anime['source']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($anime_genres)): ?>
                    <div class="detail-genres">
                        <strong>Genres:</strong>
                        <?php foreach ($anime_genres as $g): ?>
                            <a href="<?= url('genre/' . escape($g['slug'])) ?>" class="genre-tag"><?= escape($g['name']) ?></a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="detail-actions">
                    <a href="<?= url('watch/' . $slug) ?>" class="btn btn-primary btn-lg"><i class="fas fa-play"></i> Watch Now</a>
                    <?php if (is_auth()): ?>
                    <button class="btn btn-outline btn-lg" id="favBtn" data-id="<?= $anime['id'] ?>"><i class="far fa-bookmark"></i> Add to List</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Episodes List -->
    <div class="detail-section">
        <h2 class="section-title"><i class="fas fa-list"></i> Episodes (<?= $total_ep ?>)</h2>
        <div class="episode-list">
            <?php if ($total_ep > 0): ?>
                <?php foreach ($episodes as $ep):
                    $epTitle = escape($ep['title'] ?: 'Episode ' . $ep['number']);
                ?>
                <a href="<?= url('watch/' . $slug . '?ep=' . $ep['number']) ?>" class="episode-list-item">
                    <span class="ep-num"><?= $ep['number'] ?></span>
                    <div class="ep-info">
                        <span class="ep-title"><?= $epTitle ?></span>
                        <?php if ($ep['duration']): ?>
                        <span class="ep-duration"><?= gmdate('i:s', (int)$ep['duration']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="ep-badges">
                        <?php if ($ep['has_sub']): ?><span class="badge lang-badge sub-badge">SUB</span><?php endif; ?>
                        <?php if ($ep['has_dub']): ?><span class="badge lang-badge dub-badge">DUB</span><?php endif; ?>
                    </div>
                    <i class="fas fa-play ep-play-icon"></i>
                </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="empty-state">No episodes available yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Related / You May Also Like -->
    <?php if (!empty($anime_genres)):
        $genreIds = array_column($anime_genres, 'id');
        $placeholders = implode(',', array_fill(0, count($genreIds), '?'));
        $related = DB::fetchAll(
            "SELECT DISTINCT a.* FROM anime a
             JOIN anime_genre ag ON a.id = ag.anime_id
             WHERE ag.genre_id IN ($placeholders) AND a.id != ?
             ORDER BY a.views DESC LIMIT 6",
            array_merge($genreIds, [$anime['id']])
        );
        if (count($related) > 0):
    ?>
    <div class="detail-section">
        <h2 class="section-title"><i class="fas fa-link"></i> You May Also Like</h2>
        <div class="anime-grid">
            <?php foreach ($related as $a): echo anime_card($a); endforeach; ?>
        </div>
    </div>
    <?php endif; endif; ?>
</div>
