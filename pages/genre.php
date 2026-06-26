<?php
$slug = $_GET['slug'] ?? '';
$genre = DB::fetch("SELECT * FROM genres WHERE slug = ?", [$slug]);
if (!$genre): http_response_code(404); echo '<h1>Genre not found</h1>'; return; endif;

$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * ITEMS_PER_PAGE;

$total = DB::fetch(
    "SELECT COUNT(*) as count FROM anime_genre ag JOIN anime a ON a.id = ag.anime_id WHERE ag.genre_id = ?",
    [$genre['id']]
)['count'];

$anime_list = DB::fetchAll(
    "SELECT a.* FROM anime a
     JOIN anime_genre ag ON a.id = ag.anime_id
     WHERE ag.genre_id = ?
     ORDER BY a.views DESC, a.title ASC
     LIMIT ? OFFSET ?",
    [$genre['id'], ITEMS_PER_PAGE, $offset]
);

$total_pages = max(1, ceil($total / ITEMS_PER_PAGE));
?>

<div class="listing-page">
    <div class="listing-header">
        <h1><i class="fas fa-tag"></i> <?= escape($genre['name']) ?> Anime</h1>
        <p><?= $total ?> titles found</p>
    </div>

    <div class="anime-grid">
        <?php if (count($anime_list) > 0): ?>
            <?php foreach ($anime_list as $a): echo anime_card($a); endforeach; ?>
        <?php else: ?>
            <p class="empty-state">No anime found in this genre.</p>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?slug=<?= escape($slug) ?>&page=<?= $page - 1 ?>" class="page-link"><i class="fas fa-chevron-left"></i></a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?slug=<?= escape($slug) ?>&page=<?= $i ?>" class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
            <a href="?slug=<?= escape($slug) ?>&page=<?= $page + 1 ?>" class="page-link"><i class="fas fa-chevron-right"></i></a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
