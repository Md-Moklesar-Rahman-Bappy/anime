<?php
require_once __DIR__ . '/auth_check.php';
require_permission('comments.moderate');
$page_title = 'Moderate Comments';
require_once __DIR__ . '/layout.php';

$action = $_GET['action'] ?? 'list';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

if ($action === 'delete') {
    $cid = (int)($_GET['id'] ?? 0);
    $comment = DB::fetch("SELECT c.id, c.body, u.username FROM comments c INNER JOIN users u ON u.id=c.user_id WHERE c.id=?", [$cid]);
    if ($comment) {
        DB::execute("DELETE FROM comments WHERE id = ?", [$cid]);
        log_activity('Deleted comment', 'comment', $cid, ['body' => truncate($comment['body'], 50)]);
        $_SESSION['admin_success'] = 'Comment by ' . htmlspecialchars($comment['username']) . ' deleted.';
    }
    redirect(BASE_URL . '/admin/comments.php');
}

$comments = DB::fetchAll("SELECT c.*, u.username, u.avatar, a.title as anime_title, a.slug as anime_slug, e.number as ep_number FROM comments c INNER JOIN users u ON u.id=c.user_id LEFT JOIN anime a ON a.id=c.anime_id LEFT JOIN episodes e ON e.id=c.episode_id ORDER BY c.created_at DESC LIMIT $per_page OFFSET $offset");
$total = DB::fetch("SELECT COUNT(*) as cnt FROM comments")['cnt'];
$total_pages = ceil($total / $per_page);
?>
<div class="card">
    <div class="card-header"><h3 class="card-title">All Comments (<?= $total ?>)</h3></div>
    <?php if (count($comments) > 0): ?>
    <div class="table-container">
        <table>
            <thead><tr><th>User</th><th>Comment</th><th>On</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($comments as $c): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($c['username']) ?></strong></td>
                    <td style="max-width:300px;"><?= htmlspecialchars(truncate($c['body'], 80)) ?></td>
                    <td>
                        <?php if ($c['anime_title']): ?>
                        <a href="<?= BASE_URL ?>/<?= htmlspecialchars($c['anime_slug']) ?>" target="_blank" style="font-size:0.85rem;"><?= htmlspecialchars(truncate($c['anime_title'], 30)) ?></a>
                        <?php endif; ?>
                        <?php if ($c['ep_number']): ?><span class="badge badge-blue">Ep <?= $c['ep_number'] ?></span><?php endif; ?>
                    </td>
                    <td style="color:var(--text-muted);font-size:0.78rem;"><?= time_ago($c['created_at']) ?></td>
                    <td class="table-cell-actions">
                        <a href="comments.php?action=delete&id=<?=$c['id']?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this comment?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="comments.php?page=<?=$i?>" class="<?= $i===$page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php else: ?>
    <div class="empty-state"><i class="fas fa-comments"></i><p>No comments yet.</p></div>
    <?php endif; ?>
</div>
<?php
require_once __DIR__ . '/footer.php';
