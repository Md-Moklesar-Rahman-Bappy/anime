<?php
require_once __DIR__ . '/auth_check.php';
require_permission('genres.manage');
$page_title = 'Manage Genres';
require_once __DIR__ . '/layout.php';

$action = $_GET['action'] ?? 'list';

if ($action === 'create') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $slug = slugify($_POST['name']);
        DB::insert("INSERT INTO genres (name, slug) VALUES (?,?)", [$_POST['name'], $slug]);
        log_activity('Created genre', 'genre', 0, ['name' => $_POST['name']]);
        $_SESSION['admin_success'] = 'Genre "' . htmlspecialchars($_POST['name']) . '" created.';
        redirect(BASE_URL . '/admin/genres.php');
    }
?>
<div class="form-card" style="max-width:500px;">
    <form method="post">
        <div class="form-group"><label>Genre Name</label><input type="text" name="name" class="form-control" required></div>
        <div style="display:flex;gap:8px;margin-top:16px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create</button>
            <a href="genres.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php
} elseif ($action === 'edit') {
    $id = (int)($_GET['id'] ?? 0);
    $genre = DB::fetch("SELECT * FROM genres WHERE id = ?", [$id]);
    if (!$genre) { echo '<div class="alert alert-danger">Genre not found.</div>'; require __DIR__ . '/footer.php'; exit; }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $slug = slugify($_POST['name']);
        DB::execute("UPDATE genres SET name = ?, slug = ? WHERE id = ?", [$_POST['name'], $slug, $id]);
        log_activity('Updated genre', 'genre', $id, ['name' => $_POST['name']]);
        $_SESSION['admin_success'] = 'Genre updated.';
        redirect(BASE_URL . '/admin/genres.php');
    }
?>
<div class="form-card" style="max-width:500px;">
    <form method="post">
        <div class="form-group"><label>Genre Name</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($genre['name']) ?>" required></div>
        <div style="display:flex;gap:8px;margin-top:16px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
            <a href="genres.php" class="btn btn-secondary">Cancel</a>
            <?php if (user_can('genres.manage')): ?>
            <a href="genres.php?action=delete&id=<?=$id?>" class="btn btn-danger" style="margin-left:auto;" onclick="return confirm('Delete this genre?')"><i class="fas fa-trash"></i> Delete</a>
            <?php endif; ?>
        </div>
    </form>
</div>
<?php
} elseif ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    $genre = DB::fetch("SELECT name FROM genres WHERE id = ?", [$id]);
    if ($genre) {
        DB::execute("DELETE FROM genres WHERE id = ?", [$id]);
        log_activity('Deleted genre', 'genre', $id, ['name' => $genre['name']]);
        $_SESSION['admin_success'] = 'Genre "' . htmlspecialchars($genre['name']) . '" deleted.';
    }
    redirect(BASE_URL . '/admin/genres.php');

} else {
    $genres = DB::fetchAll("SELECT g.*, (SELECT COUNT(*) FROM anime_genre ag WHERE ag.genre_id = g.id) as anime_count FROM genres g ORDER BY g.name ASC");
?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Genres (<?= count($genres) ?>)</h3>
        <a href="genres.php?action=create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Genre</a>
    </div>
    <?php if (count($genres) > 0): ?>
    <table>
        <thead><tr><th>Name</th><th>Slug</th><th>Anime Count</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($genres as $g): ?>
            <tr>
                <td><strong><?= htmlspecialchars($g['name']) ?></strong></td>
                <td style="color:var(--text-muted);"><?= htmlspecialchars($g['slug']) ?></td>
                <td><span class="badge badge-purple"><?= $g['anime_count'] ?></span></td>
                <td class="table-cell-actions">
                    <a href="genres.php?action=edit&id=<?=$g['id']?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                    <a href="genres.php?action=delete&id=<?=$g['id']?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete genre <?= htmlspecialchars($g['name']) ?>?')"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty-state"><i class="fas fa-tags"></i><p>No genres yet.</p></div>
    <?php endif; ?>
</div>
<?php
}
require_once __DIR__ . '/footer.php';
