<?php
$list_type = $_GET['list'] ?? 'watching';
$valid = ['watching', 'completed', 'plan_to_watch', 'on_hold', 'dropped'];
if (!in_array($list_type, $valid)) $list_type = 'watching';
$user_id = $_SESSION['user_id'] ?? 0;

$anime_list = DB::fetchAll(
    "SELECT a.*, f.list_type, f.created_at as favorited_at FROM favorites f
     JOIN anime a ON a.id = f.anime_id
     WHERE f.user_id = ? AND f.list_type = ?
     ORDER BY f.created_at DESC", [$user_id, $list_type]
);

$counts = [];
foreach ($valid as $t) {
    $counts[$t] = DB::fetch("SELECT COUNT(*) as cnt FROM favorites WHERE user_id = ? AND list_type = ?", [$user_id, $t])['cnt'];
}
?>
<section class="section">
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-bookmark"></i> My List</h2>
    </div>
    <div class="my-list-tabs" style="display:flex;gap:4px;margin-bottom:20px;flex-wrap:wrap;">
        <?php $labels = ['watching' => 'Watching', 'completed' => 'Completed', 'plan_to_watch' => 'Plan to Watch', 'on_hold' => 'On Hold', 'dropped' => 'Dropped']; ?>
        <?php foreach ($labels as $key => $label): ?>
        <a href="?list=<?= $key ?>" class="tab-btn <?= $list_type === $key ? 'active' : '' ?>">
            <?= $label ?> <span class="count">(<?= $counts[$key] ?? 0 ?>)</span>
        </a>
        <?php endforeach; ?>
    </div>
    <?php if (count($anime_list) > 0): ?>
    <div class="anime-grid">
        <?php foreach ($anime_list as $a): echo anime_card($a); endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state"><i class="fas fa-inbox"></i><p>Nothing in this list yet. Browse anime to add some!</p><a href="<?= url('') ?>" class="btn btn-primary">Browse Anime</a></div>
    <?php endif; ?>
</section>
