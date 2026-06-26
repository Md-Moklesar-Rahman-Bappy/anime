<?php
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * ITEMS_PER_PAGE;

$q      = $_GET['q'] ?? '';
$az     = $_GET['az'] ?? '';
$status = $_GET['status'] ?? '';
$type   = $_GET['type'] ?? '';
$genre  = $_GET['genre'] ?? '';
$year   = $_GET['year'] ?? '';
$season = $_GET['season'] ?? '';
$sort   = $_GET['sort'] ?? 'title';
$order  = $_GET['order'] ?? 'ASC';

// Build query
$where  = [];
$params = [];

if ($q) {
    $where[] = "(a.title LIKE ? OR a.title_japanese LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($az) {
    if ($az === '0-9') {
        $where[] = "a.title REGEXP '^[0-9]'";
    } else {
        $where[] = "a.title LIKE ?";
        $params[] = $az . '%';
    }
}
if ($status) {
    $where[] = "a.status = ?";
    $params[] = $status;
}
if ($type) {
    $where[] = "a.type = ?";
    $params[] = $type;
}
if ($year) {
    $where[] = "a.year = ?";
    $params[] = (int)$year;
}
if ($season) {
    $where[] = "a.season = ?";
    $params[] = $season;
}

// Genre filter via subquery
$genreJoin = '';
if ($genre) {
    $genreJoin = "JOIN anime_genre ag2 ON a.id = ag2.anime_id";
    $where[] = "ag2.genre_id = (SELECT id FROM genres WHERE slug = ?)";
    $params[] = $genre;
}

$whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// Count
$total = DB::fetch(
    "SELECT COUNT(DISTINCT a.id) as count FROM anime a $genreJoin $whereClause", $params
)['count'];
$total_pages = max(1, ceil($total / ITEMS_PER_PAGE));

// Sort
$sortMap = [
    'title'    => 'a.title',
    'newest'   => 'a.created_at',
    'updated'  => 'a.updated_at',
    'rating'   => 'a.rating',
    'score'    => 'a.score',
    'views'    => 'a.views',
    'year'     => 'a.year',
    'episodes' => 'a.episodes_count',
];
$sortCol = $sortMap[$sort] ?? 'a.title';
$sortDir = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

$anime_list = DB::fetchAll(
    "SELECT DISTINCT a.* FROM anime a $genreJoin $whereClause
     ORDER BY $sortCol $sortDir, a.id ASC
     LIMIT ? OFFSET ?",
    array_merge($params, [ITEMS_PER_PAGE, $offset])
);

$genres = get_genres();
$years = range(date('Y'), 1980);
?>

<div class="listing-page">
    <div class="listing-header">
        <h1><i class="fas fa-filter"></i> Browse Anime</h1>
        <p><?= $total ?> titles found</p>
    </div>

    <!-- Filters -->
    <form class="filter-bar" method="GET">
        <div class="filter-group">
            <input type="text" name="q" class="filter-input" placeholder="Search..." value="<?= escape($q) ?>">
        </div>
        <div class="filter-group">
            <select name="status" class="filter-select">
                <option value="">All Status</option>
                <option value="Currently Airing" <?= $status === 'Currently Airing' ? 'selected' : '' ?>>Airing</option>
                <option value="Finished Airing" <?= $status === 'Finished Airing' ? 'selected' : '' ?>>Finished</option>
                <option value="Not yet aired" <?= $status === 'Not yet aired' ? 'selected' : '' ?>>Upcoming</option>
            </select>
        </div>
        <div class="filter-group">
            <select name="type" class="filter-select">
                <option value="">All Types</option>
                <option value="TV" <?= $type === 'TV' ? 'selected' : '' ?>>TV</option>
                <option value="Movie" <?= $type === 'Movie' ? 'selected' : '' ?>>Movie</option>
                <option value="OVA" <?= $type === 'OVA' ? 'selected' : '' ?>>OVA</option>
                <option value="ONA" <?= $type === 'ONA' ? 'selected' : '' ?>>ONA</option>
                <option value="Special" <?= $type === 'Special' ? 'selected' : '' ?>>Special</option>
            </select>
        </div>
        <div class="filter-group">
            <select name="season" class="filter-select">
                <option value="">All Seasons</option>
                <option value="Spring" <?= $season === 'Spring' ? 'selected' : '' ?>>Spring</option>
                <option value="Summer" <?= $season === 'Summer' ? 'selected' : '' ?>>Summer</option>
                <option value="Fall" <?= $season === 'Fall' ? 'selected' : '' ?>>Fall</option>
                <option value="Winter" <?= $season === 'Winter' ? 'selected' : '' ?>>Winter</option>
            </select>
        </div>
        <div class="filter-group">
            <select name="year" class="filter-select">
                <option value="">All Years</option>
                <?php foreach ($years as $y): ?>
                    <option value="<?= $y ?>" <?= (int)$year === $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <select name="genre" class="filter-select">
                <option value="">All Genres</option>
                <?php foreach ($genres as $g): ?>
                    <option value="<?= escape($g['slug']) ?>" <?= $genre === $g['slug'] ? 'selected' : '' ?>><?= escape($g['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <select name="sort" class="filter-select">
                <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>Name A-Z</option>
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                <option value="updated" <?= $sort === 'updated' ? 'selected' : '' ?>>Updated</option>
                <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Rating</option>
                <option value="score" <?= $sort === 'score' ? 'selected' : '' ?>>Score</option>
                <option value="views" <?= $sort === 'views' ? 'selected' : '' ?>>Most Viewed</option>
                <option value="year" <?= $sort === 'year' ? 'selected' : '' ?>>Year</option>
                <option value="episodes" <?= $sort === 'episodes' ? 'selected' : '' ?>>Episodes</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
        <a href="<?= url('filter') ?>" class="btn btn-outline"><i class="fas fa-undo"></i> Reset</a>
    </form>

    <div class="anime-grid">
        <?php if (count($anime_list) > 0): ?>
            <?php foreach ($anime_list as $a): echo anime_card($a); endforeach; ?>
        <?php else: ?>
            <p class="empty-state">No anime found matching your criteria.</p>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="page-link"><i class="fas fa-chevron-left"></i></a>
        <?php endif; ?>
        <?php
        $start = max(1, $page - 2);
        $end = min($total_pages, $page + 2);
        for ($i = $start; $i <= $end; $i++):
        ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="page-link"><i class="fas fa-chevron-right"></i></a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
