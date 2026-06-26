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
    <?php foreach (['pending' => '🟡 Pending', 'resolved' => '✅ Resolved', 'dismissed' => '❌ Dismissed'] as $s => $label): ?>
    <a href="reports.php?status=<?= $s ?>" class="btn btn-sm <?= $status_filter === $s ? 'btn-primary' : 'btn-outline' ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>
<div class="card">
    <div class="card-header"><h3 class="card-title"><?= ucfirst($status_filter) ?> Reports (<?= count($reports) ?>)</h3></div>
    <?php if (count($reports) > 0): ?>
    <table>
        <thead>
            <tr><th>ID</th><th>Anime</th><th>Episode</th><th>Type</th><th>Reported by</th><th>Description</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($reports as $r): ?>
        <tr>
            <td style="font-weight:700;color:var(--text-muted);">#<?= $r['id'] ?></td>
            <td><a href="<?= BASE_URL ?>/admin/anime.php?action=edit&id=<?= $r['anime_id'] ?>" class="table-link"><?= htmlspecialchars($r['anime_title'] ?: '-') ?></a></td>
            <td><span class="badge badge-blue">Ep <?= $r['ep_num'] ?: '-' ?></span></td>
            <td>
                <?php
                $type_colors = [
                    'broken_video' => '#ef4444', 'wrong_episode' => '#f59e0b',
                    'subtitle_issue' => '#8b5cf6', 'audio_issue' => '#ec4899',
                    'wrong_source' => '#f97316', 'other' => '#6b7280'
                ];
                $tc = $type_colors[$r['type']] ?? '#6b7280';
                ?>
                <span class="badge" style="background:<?= $tc ?>"><?= htmlspecialchars(str_replace('_', ' ', $r['type'])) ?></span>
            </td>
            <td><span style="color:var(--text-muted);font-size:0.85rem;"><?= htmlspecialchars($r['reporter'] ?: 'Guest') ?></span></td>
            <td style="max-width:220px;font-size:0.82rem;color:var(--text-muted);line-height:1.4;"><?= htmlspecialchars(truncate($r['description'] ?? '', 120)) ?></td>
            <td style="font-size:0.78rem;color:var(--text-muted);white-space:nowrap;"><?= time_ago($r['created_at']) ?></td>
            <td class="table-cell-actions" style="white-space:nowrap;">
                <?php if ($r['status'] === 'pending'): ?>
                <form method="post" style="display:inline" onsubmit="return confirm('Mark as resolved?')">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <input type="hidden" name="status" value="resolved">
                    <button type="submit" name="action" value="update_status" class="btn btn-sm btn-success" title="Resolve"><i class="fas fa-check"></i></button>
                </form>
                <form method="post" style="display:inline" onsubmit="return confirm('Dismiss this report?')">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <input type="hidden" name="status" value="dismissed">
                    <button type="submit" name="action" value="update_status" class="btn btn-sm btn-danger" title="Dismiss"><i class="fas fa-times"></i></button>
                </form>
                <?php else: ?>
                <span class="badge <?= $r['status']==='resolved'?'badge-green':'badge-gray' ?>"><?= ucfirst($r['status']) ?></span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty-state"><i class="fas fa-check-circle"></i><p>No <?= $status_filter ?> reports.</p></div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
