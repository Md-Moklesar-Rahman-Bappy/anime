<?php
require_once __DIR__ . '/auth_check.php';
require_permission('settings.manage');
$page_title = 'Anime Requests';
require_once __DIR__ . '/layout.php';

$action = $_GET['action'] ?? 'list';

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? 'pending';
    $response = trim($_POST['admin_response'] ?? '');
    if (in_array($status, ['approved', 'rejected', 'pending'])) {
        DB::execute("UPDATE anime_requests SET status = ?, admin_response = ? WHERE id = ?",
            [$status, $response, $id]);
        $_SESSION['admin_success'] = 'Request #' . $id . ' updated.';
        log_activity('Updated anime request', 'request', $id, ['status' => $status]);
    }
    redirect(BASE_URL . '/admin/requests.php');
}

$status_filter = $_GET['status'] ?? 'pending';
$requests = DB::fetchAll(
    "SELECT r.*, u.username FROM anime_requests r
     LEFT JOIN users u ON u.id = r.user_id
     WHERE r.status = ?
     ORDER BY r.created_at DESC LIMIT 100",
    [$status_filter]
);
?>
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
    <?php foreach (['pending', 'approved', 'rejected'] as $s): ?>
    <a href="requests.php?status=<?= $s ?>" class="btn btn-sm <?= $status_filter === $s ? 'btn-primary' : 'btn-outline' ?>"><?= ucfirst($s) ?></a>
    <?php endforeach; ?>
</div>
<div class="card">
    <div class="card-header"><h3 class="card-title"><?= ucfirst($status_filter) ?> Requests (<?= count($requests) ?>)</h3></div>
    <?php if (count($requests) > 0): ?>
    <table><thead><tr><th>ID</th><th>Title</th><th>User</th><th>Description</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($requests as $r): ?>
        <tr>
            <td>#<?= $r['id'] ?></td>
            <td><strong><?= htmlspecialchars($r['title']) ?></strong></td>
            <td><?= htmlspecialchars($r['username'] ?: 'Guest') ?></td>
            <td style="max-width:200px;font-size:0.8rem;color:var(--text-muted);"><?= htmlspecialchars(truncate($r['description'] ?? '', 80)) ?></td>
            <td style="font-size:0.78rem;"><?= time_ago($r['created_at']) ?></td>
            <td><span class="badge <?= $r['status']==='approved'?'badge-green':($r['status']==='rejected'?'badge-danger':'badge-orange') ?>"><?= ucfirst($r['status']) ?></span></td>
            <td class="table-cell-actions">
                <button class="btn btn-sm btn-info" onclick="document.getElementById('reqForm<?= $r['id'] ?>').style.display='block'">
                    <i class="fas fa-reply"></i> Respond
                </button>
                <form id="reqForm<?= $r['id'] ?>" method="post" style="display:none;margin-top:8px;">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <select name="status" class="form-control" style="width:120px;display:inline-block;">
                        <option value="approved" <?= $r['status']==='approved'?'selected':''?>>Approve</option>
                        <option value="rejected" <?= $r['status']==='rejected'?'selected':''?>>Reject</option>
                        <option value="pending" <?= $r['status']==='pending'?'selected':''?>>Pending</option>
                    </select>
                    <input type="text" name="admin_response" class="form-control" placeholder="Response (optional)" style="width:200px;display:inline-block;" value="<?= htmlspecialchars($r['admin_response'] ?? '') ?>">
                    <button type="submit" name="action" value="update" class="btn btn-sm btn-primary"><i class="fas fa-save"></i></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody></table>
    <?php else: ?>
    <div class="empty-state"><i class="fas fa-inbox"></i><p>No <?= $status_filter ?> requests.</p></div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
