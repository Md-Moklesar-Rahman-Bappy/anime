<?php
// ---- HOMEPAGE DATA ----

// Featured slider (top 5 featured)
$featured = DB::fetchAll(
    "SELECT * FROM anime WHERE featured = 1 ORDER BY featured_order ASC, id ASC LIMIT 5"
);

// Latest episodes (latest 12)
$latest_episodes = DB::fetchAll(
    "SELECT e.* FROM episodes e
     ORDER BY e.created_at DESC
     LIMIT 12"
);

// Top rated (all time) — used in right sidebar
$top_rated = DB::fetchAll(
    "SELECT * FROM anime ORDER BY score DESC, rating DESC LIMIT 10"
);

// Latest anime (newest additions)
$latest_anime = DB::fetchAll(
    "SELECT * FROM anime ORDER BY created_at DESC LIMIT 12"
);

// Ongoing anime
$ongoing_anime = DB::fetchAll(
    "SELECT * FROM anime WHERE status = 'Currently Airing' ORDER BY updated_at DESC LIMIT 12"
);

// Upcoming anime
$upcoming_anime = DB::fetchAll(
    "SELECT * FROM anime WHERE status = 'Not yet aired' ORDER BY created_at DESC LIMIT 12"
);

// Current tab
$tab = $_GET['tab'] ?? 'all';
?>

<?php if (!empty($featured)): ?>
<section class="hero-slider-section">
    <div class="hero-slider" id="heroSlider">
        <?php foreach ($featured as $i => $feat):
            $bg = $feat['banner'] ?: ($feat['thumbnail'] ?: '');
            $title = escape($feat['title']);
            $slug = escape($feat['slug']);
            $desc = truncate($feat['description'], 200);
            $rating = $feat['rating'] ?? '?';
            $year = $feat['year'] ?? '';
            $type = $feat['type'] ?? '';
            $ep = $feat['episodes_count'] ?? 0;
        ?>
        <div class="hero-slide <?= $i === 0 ? 'active' : '' ?>" style="background-image:linear-gradient(135deg, rgba(10,10,25,0.95) 0%, rgba(10,10,25,0.4) 50%, rgba(10,10,25,0.85) 100%),url('<?= escape($bg) ?>')">
            <div class="hero-content">
                <div class="hero-badges">
                    <span class="badge type-badge"><?= $type ?></span>
                    <span class="badge ep-badge"><?= $ep ?> Episodes</span>
                    <span class="badge rating-badge"><i class="fas fa-star"></i> <?= $rating ?></span>
                </div>
                <h1 class="hero-title"><?= $title ?></h1>
                <p class="hero-desc"><?= $desc ?></p>
                <div class="hero-actions">
                    <a href="<?= url('watch/' . $slug) ?>" class="btn btn-primary"><i class="fas fa-play"></i> Watch Now</a>
                    <a href="<?= url($slug) ?>" class="btn btn-outline"><i class="fas fa-info-circle"></i> Details</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="hero-nav" id="heroNav">
        <?php foreach ($featured as $i => $feat): ?>
            <button class="hero-dot <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>"></button>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<div class="home-layout">
<div class="home-content">

<!-- Latest Episodes -->
<section class="section">
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-clock"></i> Latest Episodes</h2>
        <div class="section-tabs">
            <a href="?tab=all" class="tab-btn <?= $tab === 'all' ? 'active' : '' ?>">All</a>
            <a href="?tab=sub" class="tab-btn <?= $tab === 'sub' ? 'active' : '' ?>">Sub</a>
            <a href="?tab=dub" class="tab-btn <?= $tab === 'dub' ? 'active' : '' ?>">Dub</a>
            <a href="?tab=trending" class="tab-btn <?= $tab === 'trending' ? 'active' : '' ?>">Trending</a>
            <a href="<?= url('random') ?>" class="tab-btn">Random</a>
        </div>
        <a href="<?= url('filter?sort=updated') ?>" class="section-link">View all <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="episode-grid">
        <?php
        $items = $latest_episodes;
        if ($tab === 'sub') $items = array_filter($items, fn($e) => $e['has_sub']);
        if ($tab === 'dub') $items = array_filter($items, fn($e) => $e['has_dub']);
        if ($tab === 'trending') $items = DB::fetchAll("SELECT e.* FROM episodes e ORDER BY e.views DESC LIMIT 12");

        if (count($items) > 0):
            foreach ($items as $ep): echo episode_card($ep); endforeach;
        else:
            echo '<p class="empty-state">No episodes found.</p>';
        endif;
        ?>
    </div>
</section>

<!-- Latest Anime (New Releases) -->
<section class="section">
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-sparkles"></i> New Releases</h2>
        <a href="<?= url('filter?sort=newest') ?>" class="section-link">View all <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="anime-grid">
        <?php foreach ($latest_anime as $a): echo anime_card($a); endforeach; ?>
    </div>
</section>

<!-- Ongoing / Upcoming -->
<section class="section">
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-broadcast-tower"></i> Ongoing Anime</h2>
        <a href="<?= url('filter?status=Currently Airing') ?>" class="section-link">View all <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="anime-grid">
        <?php if (count($ongoing_anime) > 0): ?>
            <?php foreach ($ongoing_anime as $a): echo anime_card($a); endforeach; ?>
        <?php else: ?>
            <p class="empty-state">No ongoing anime at the moment.</p>
        <?php endif; ?>
    </div>
</section>

<?php if (count($upcoming_anime) > 0): ?>
<section class="section">
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-calendar"></i> Upcoming Anime</h2>
        <a href="<?= url('filter?status=Not yet aired') ?>" class="section-link">View all <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="anime-grid">
        <?php foreach ($upcoming_anime as $a): echo anime_card($a); endforeach; ?>
    </div>
</section>
<?php endif; ?>

</div><!-- /.home-content -->

<div class="home-sidebar">
    <div class="home-sidebar-section">
        <h3 class="home-sidebar-heading"><i class="fas fa-trophy"></i> Top Anime</h3>
        <div class="top-anime-list">
            <?php foreach ($top_rated as $i => $a):
                $thumb = $a['thumbnail'] ?: 'https://via.placeholder.com/50x70/1a1a2e/cccccc?text=N';
            ?>
            <a href="<?= url($a['slug']) ?>" class="top-anime-item">
                <span class="top-rank"><?= $i + 1 ?></span>
                <img src="<?= escape($thumb) ?>" alt="<?= escape($a['title']) ?>" class="top-thumb" loading="lazy">
                <div class="top-info">
                    <h4><?= escape($a['title']) ?></h4>
                    <span class="top-meta"><?= $a['type'] ?? '' ?> &middot; <i class="fas fa-star"></i> <?= $a['rating'] ?? '?' ?></span>
                </div>
                <span class="top-score"><?= $a['score'] ?? $a['rating'] ?? '?' ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</div><!-- /.home-layout -->
