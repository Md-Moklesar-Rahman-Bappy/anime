<?php
require_once __DIR__ . '/auth_check.php';
require_permission('users.view');
$page_title = 'Manage Users';
require_once __DIR__ . '/layout.php';

$action = $_GET['action'] ?? 'list';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

if ($action === 'edit' && user_can('users.edit')) {
    $uid = (int)($_GET['id'] ?? 0);
    $target = DB::fetch("SELECT * FROM users WHERE id = ?", [$uid]);
    if (!$target) { echo '<div class="alert alert-danger">User not found.</div>'; require __DIR__ . '/footer.php'; exit; }
    $page_title = 'Edit User: ' . htmlspecialchars($target['username']);
    $roles = DB::fetchAll("SELECT * FROM roles ORDER BY level");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $updates = "username = ?, email = ?";
        $params = [$_POST['username'], $_POST['email']];
        if (!empty($_POST['password'])) {
            $updates .= ", password = ?";
            $params[] = password_hash($_POST['password'], PASSWORD_BCRYPT);
        }
        if (user_can('users.edit') && $GLOBALS['_role_level'] >= 2) {
            $updates .= ", role_id = ?";
            $params[] = !empty($_POST['role_id']) ? (int)$_POST['role_id'] : null;
        }
        $params[] = $uid;
        DB::execute("UPDATE users SET $updates WHERE id = ?", $params);
        log_activity('Updated user', 'user', $uid, ['username' => $_POST['username']]);
        $_SESSION['admin_success'] = 'User updated.';
        redirect(BASE_URL . '/admin/users.php?action=edit&id=' . $uid);
    }

    $user_role = DB::fetch("SELECT * FROM roles WHERE id = ?", [$target['role_id']]);
?>
<div class="form-card">
    <form method="post">
        <div class="form-row">
            <div class="form-group"><label>Username</label><input type="text" name="username" class="form-control" value="<?= htmlspecialchars($target['username']) ?>" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($target['email']) ?>" required></div>
        </div>
        <div class="form-group"><label>New Password (leave blank to keep current)</label><input type="password" name="password" class="form-control" placeholder="Enter new password"></div>
        <?php if ($GLOBALS['_role_level'] >= 2): ?>
        <div class="form-group"><label>Role</label>
            <select name="role_id" class="form-control">
                <option value="">No role (basic user)</option>
                <?php foreach ($roles as $r): $sel = ($r['id']==$target['role_id'])?'selected':''; ?>
                <option value="<?=$r['id']?>" <?=$sel?>><?= htmlspecialchars($r['name']) ?> (Level <?=$r['level']?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div style="display:flex;gap:8px;margin-top:16px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
            <a href="users.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php
} elseif ($action === 'delete' && user_can('users.delete')) {
    $uid = (int)($_GET['id'] ?? 0);
    if ($uid === (int)($GLOBALS['_user']['id'] ?? 0)) {
        $_SESSION['admin_error'] = 'You cannot delete your own account.';
    } else {
        $target = DB::fetch("SELECT username FROM users WHERE id = ?", [$uid]);
        if ($target) {
            DB::execute("DELETE FROM users WHERE id = ?", [$uid]);
            log_activity('Deleted user', 'user', $uid, ['username' => $target['username']]);
            $_SESSION['admin_success'] = 'User "' . htmlspecialchars($target['username']) . '" deleted.';
        }
    }
    redirect(BASE_URL . '/admin/users.php');

} else {
    $search = $_GET['search'] ?? '';
    $where = '';
    $params = [];
    if ($search) {
        $where = 'WHERE (username LIKE ? OR email LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }
    $total = DB::fetch("SELECT COUNT(*) as cnt FROM users $where", $params)['cnt'];
    $users = DB::fetchAll("SELECT u.*, r.name as role_name, r.slug as role_slug FROM users u LEFT JOIN roles r ON r.id = u.role_id $where ORDER BY u.created_at DESC LIMIT $per_page OFFSET $offset", $params);
    $total_pages = ceil($total / $per_page);
?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Users (<?= $total ?>)</h3>
    </div>
    <form method="get" class="search-box">
        <input type="text" name="search" class="form-control" placeholder="Search by username or email..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i></button>
        <?php if ($search): ?><a href="users.php" class="btn btn-secondary">Clear</a><?php endif; ?>
    </form>
    <?php if (count($users) > 0): ?>
    <table>
        <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Registered</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td>#<?= $u['id'] ?></td>
                <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td>
                    <?php if ($u['role_slug']): ?>
                    <span class="user-role-badge role-<?= $u['role_slug'] ?>"><?= htmlspecialchars($u['role_name']) ?></span>
                    <?php else: ?>
                    <span class="badge badge-gray">User</span>
                    <?php endif; ?>
                </td>
                <td style="color:var(--text-muted);font-size:0.78rem;"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                <td class="table-cell-actions">
                    <?php if (user_can('users.edit')): ?><a href="users.php?action=edit&id=<?=$u['id']?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a><?php endif; ?>
                    <?php if (user_can('users.delete') && $u['id'] !== (int)($GLOBALS['_user']['id'] ?? 0)): ?>
                    <a href="users.php?action=delete&id=<?=$u['id']?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete user <?= htmlspecialchars($u['username']) ?>?')"><i class="fas fa-trash"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="users.php?page=<?=$i?><?= $search ? '&search='.urlencode($search) : '' ?>" class="<?= $i===$page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php else: ?>
    <div class="empty-state"><i class="fas fa-users"></i><p>No users found.</p></div>
    <?php endif; ?>
</div>
<?php
}
require_once __DIR__ . '/footer.php';
