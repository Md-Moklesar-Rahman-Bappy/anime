<?php
require_once __DIR__ . '/auth_check.php';
require_permission('settings.manage');
$page_title = 'Manage Reports';
require_once __DIR__ . '/layout.php';

$action = $_GET['action'] ?? 'list';

if ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? 'pending';
    if (in_array($status, ['pending', 'resolved', 'dismissed'])) {
        DB::execute("UPDATE reports SET status = ? WHERE id = ?", [$status, $id]);
        $_SESSION['admin_success'] = 'Report #' . $id . ' ' . $status . '.';
    }
    redirect(BASE_URL . '/admin/reports.php');
}

$status_filter = $_GET['status'] ?? 'pending';
$reports = DB::fetchAll(
    "SELECT r.*, u.username as reporter, e.number as ep_num, a.title as anime_title, a.slug as anime_slug
     FROM reports r
     LEFT JOIN users u ON u.id = r.user_id
     LEFT JOIN episodes e ON e.id = r.episode_id
     LEFT JOIN anime a ON a.id = r.anime_id
     WHERE r.status = ?
     ORDER BY r.created_at DESC LIMIT 100",
    [$status_filter]
);
?>
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
    <?php foreach (['pending', 'resolved', 'dismissed'] as $s): ?>
    <a href="reports.php?status=<?= $s ?>" class="btn btn-sm <?= $status_filter === $s ? 'btn-primary' : 'btn-outline' ?>"><?= ucfirst($s) ?></a>
    <?php endforeach; ?>
</div>
<div class="card">
    <div class="card-header"><h3 class="card-title"><?= ucfirst($status_filter) ?> Reports (<?= count($reports) ?>)</h3></div>
    <?php if (count($reports) > 0): ?>
    <table><thead><tr><th>ID</th><th>Anime</th><th>Episode</th><th>Type</th><th>Reported by</th><th>Description</th><th>Date</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($reports as $r): ?>
        <tr>
            <td>#<?= $r['id'] ?></td>
            <td><a href="<?= BASE_URL ?>/admin/anime.php?action=edit&id=<?= $r['anime_id'] ?>"><?= htmlspecialchars($r['anime_title'] ?: '-') ?></a></td>
            <td>Ep <?= $r['ep_num'] ?: '-' ?></td>
            <td><span class="badge badge-orange"><?= htmlspecialchars(str_replace('_', ' ', $r['type'])) ?></span></td>
            <td><?= htmlspecialchars($r['reporter'] ?: 'Guest') ?></td>
            <td style="max-width:200px;font-size:0.8rem;color:var(--text-muted);"><?= htmlspecialchars(truncate($r['description'] ?? '', 80)) ?></td>
            <td style="font-size:0.78rem;color:var(--text-muted);"><?= time_ago($r['created_at']) ?></td>
            <td class="table-cell-actions">
                <?php if ($r['status'] === 'pending'): ?>
                <form method="post" style="display:inline">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <input type="hidden" name="status" value="resolved">
                    <button type="submit" name="action" value="update_status" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                </form>
                <form method="post" style="display:inline">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <input type="hidden" name="status" value="dismissed">
                    <button type="submit" name="action" value="update_status" class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button>
                </form>
                <?php else: ?>
                <span class="badge <?= $r['status']==='resolved'?'badge-green':'badge-gray' ?>"><?= ucfirst($r['status']) ?></span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody></table>
    <?php else: ?>
    <div class="empty-state"><i class="fas fa-check-circle"></i><p>No <?= $status_filter ?> reports.</p></div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
