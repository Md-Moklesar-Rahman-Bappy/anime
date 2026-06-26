<?php
require_once __DIR__ . '/auth_check.php';
$action = $_GET['action'] ?? 'list';
$page_title = 'Manage Anime';
require_once __DIR__ . '/layout.php';

if ($action === 'create' && user_can('anime.create')) {
    $page_title = 'Add New Anime';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $slug = slugify($_POST['title']);
        $id = DB::insert(
            "INSERT INTO anime (title, title_japanese, slug, description, type, status, country, season, year, rating, age_rating, episodes_count, duration, source, studio, producers, licensors, thumbnail, banner, featured) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            [
                $_POST['title'], $_POST['title_japanese'], $slug, $_POST['description'],
                $_POST['type'], $_POST['status'], $_POST['country'], $_POST['season'],
                $_POST['year'] ?: null, $_POST['rating'] ?: null, $_POST['age_rating'],
                (int)$_POST['episodes_count'], (int)$_POST['duration'] ?: null,
                $_POST['source'], $_POST['studio'], $_POST['producers'], $_POST['licensors'],
                $_POST['thumbnail'], $_POST['banner'], isset($_POST['featured']) ? 1 : 0
            ]
        );
        if (!empty($_POST['genres'])) {
            foreach ($_POST['genres'] as $gid) {
                DB::execute("INSERT INTO anime_genre (anime_id, genre_id) VALUES (?,?)", [$id, (int)$gid]);
            }
        }
        log_activity('Created anime', 'anime', $id, ['title' => $_POST['title']]);
        $_SESSION['admin_success'] = 'Anime created successfully.';
        redirect(BASE_URL . '/admin/anime.php?action=edit&id=' . $id);
    }
    $genres = DB::fetchAll("SELECT * FROM genres ORDER BY name");
?>
<div class="form-card">
    <form method="post">
        <div class="form-row">
            <div class="form-group"><label>Title *</label><input type="text" name="title" class="form-control" required></div>
            <div class="form-group"><label>Japanese Title</label><input type="text" name="title_japanese" class="form-control"></div>
        </div>
        <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="4"></textarea></div>
        <div class="form-row">
            <div class="form-group"><label>Type</label>
                <select name="type" class="form-control"><option value="">Select</option><?php foreach(['TV','Movie','OVA','ONA','Special','Music'] as $t): ?><option value="<?=$t?>"><?=$t?></option><?php endforeach; ?></select>
            </div>
            <div class="form-group"><label>Status</label>
                <select name="status" class="form-control"><option value="">Select</option><?php foreach(['Currently Airing','Finished Airing','Not yet aired'] as $s): ?><option value="<?=$s?>"><?=$s?></option><?php endforeach; ?></select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Year</label><input type="number" name="year" class="form-control" min="1900" max="2099"></div>
            <div class="form-group"><label>Season</label>
                <select name="season" class="form-control"><option value="">Select</option><?php foreach(['Spring','Summer','Fall','Winter'] as $s): ?><option value="<?=$s?>"><?=$s?></option><?php endforeach; ?></select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Episodes Count</label><input type="number" name="episodes_count" class="form-control" value="0" min="0"></div>
            <div class="form-group"><label>Duration (min)</label><input type="number" name="duration" class="form-control" min="0"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Rating (1-10)</label><input type="number" step="0.1" name="rating" class="form-control" min="0" max="10"></div>
            <div class="form-group"><label>Age Rating</label>
                <select name="age_rating" class="form-control"><option value="">Select</option><?php foreach(['G','PG','PG-13','R','R+','Rx'] as $r): ?><option value="<?=$r?>"><?=$r?></option><?php endforeach; ?></select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Source</label><input type="text" name="source" class="form-control" placeholder="e.g. Manga, Original"></div>
            <div class="form-group"><label>Studio</label><input type="text" name="studio" class="form-control"></div>
        </div>
        <div class="form-group"><label>Thumbnail URL</label><input type="url" name="thumbnail" class="form-control" placeholder="https://..."></div>
        <div class="form-group"><label>Banner URL</label><input type="url" name="banner" class="form-control" placeholder="https://..."></div>
        <div class="form-group">
            <label>Genres</label>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                <?php foreach ($genres as $g): ?>
                <label class="form-check"><input type="checkbox" name="genres[]" value="<?=$g['id']?>"> <?= htmlspecialchars($g['name']) ?></label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="form-group"><label class="form-check"><input type="checkbox" name="featured" value="1"> Featured / Promoted</label></div>
        <div style="display:flex;gap:8px;margin-top:16px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create Anime</button>
            <a href="anime.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php
} elseif ($action === 'edit' && user_can('anime.edit')) {
    $id = (int)($_GET['id'] ?? 0);
    $anime = DB::fetch("SELECT * FROM anime WHERE id = ?", [$id]);
    if (!$anime) { echo '<div class="alert alert-danger">Anime not found.</div>'; require __DIR__ . '/footer.php'; exit; }
    $page_title = 'Edit: ' . htmlspecialchars($anime['title']);
    $selected_genres = DB::fetchAll("SELECT genre_id FROM anime_genre WHERE anime_id = ?", [$id]);
    $selected_ids = array_column($selected_genres, 'genre_id');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        DB::execute(
            "UPDATE anime SET title=?, title_japanese=?, description=?, type=?, status=?, country=?, season=?, year=?, rating=?, age_rating=?, episodes_count=?, duration=?, source=?, studio=?, producers=?, licensors=?, thumbnail=?, banner=?, featured=? WHERE id=?",
            [
                $_POST['title'], $_POST['title_japanese'], $_POST['description'],
                $_POST['type'], $_POST['status'], $_POST['country'], $_POST['season'],
                $_POST['year'] ?: null, $_POST['rating'] ?: null, $_POST['age_rating'],
                (int)$_POST['episodes_count'], (int)$_POST['duration'] ?: null,
                $_POST['source'], $_POST['studio'], $_POST['producers'], $_POST['licensors'],
                $_POST['thumbnail'], $_POST['banner'], isset($_POST['featured']) ? 1 : 0, $id
            ]
        );
        DB::execute("DELETE FROM anime_genre WHERE anime_id = ?", [$id]);
        if (!empty($_POST['genres'])) {
            foreach ($_POST['genres'] as $gid) {
                DB::execute("INSERT INTO anime_genre (anime_id, genre_id) VALUES (?,?)", [$id, (int)$gid]);
            }
        }
        log_activity('Updated anime', 'anime', $id, ['title' => $_POST['title']]);
        $_SESSION['admin_success'] = 'Anime updated successfully.';
        redirect(BASE_URL . '/admin/anime.php?action=edit&id=' . $id);
    }

    $genres = DB::fetchAll("SELECT * FROM genres ORDER BY name");
?>
<div class="form-card">
    <form method="post">
        <div class="form-row">
            <div class="form-group"><label>Title *</label><input type="text" name="title" class="form-control" value="<?= htmlspecialchars($anime['title']) ?>" required></div>
            <div class="form-group"><label>Japanese Title</label><input type="text" name="title_japanese" class="form-control" value="<?= htmlspecialchars($anime['title_japanese'] ?? '') ?>"></div>
        </div>
        <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($anime['description'] ?? '') ?></textarea></div>
        <div class="form-row">
            <div class="form-group"><label>Type</label>
                <select name="type" class="form-control"><option value="">Select</option><?php foreach(['TV','Movie','OVA','ONA','Special','Music'] as $t): $sel = ($anime['type']===$t)?'selected':''; ?><option <?=$sel?> value="<?=$t?>"><?=$t?></option><?php endforeach; ?></select>
            </div>
            <div class="form-group"><label>Status</label>
                <select name="status" class="form-control"><option value="">Select</option><?php foreach(['Currently Airing','Finished Airing','Not yet aired'] as $s): $sel = ($anime['status']===$s)?'selected':''; ?><option <?=$sel?> value="<?=$s?>"><?=$s?></option><?php endforeach; ?></select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Year</label><input type="number" name="year" class="form-control" value="<?= htmlspecialchars($anime['year'] ?? '') ?>" min="1900" max="2099"></div>
            <div class="form-group"><label>Season</label>
                <select name="season" class="form-control"><option value="">Select</option><?php foreach(['Spring','Summer','Fall','Winter'] as $s): $sel = ($anime['season']===$s)?'selected':''; ?><option <?=$sel?> value="<?=$s?>"><?=$s?></option><?php endforeach; ?></select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Episodes</label><input type="number" name="episodes_count" class="form-control" value="<?= (int)$anime['episodes_count'] ?>" min="0"></div>
            <div class="form-group"><label>Duration (min)</label><input type="number" name="duration" class="form-control" value="<?= htmlspecialchars($anime['duration'] ?? '') ?>" min="0"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Rating</label><input type="number" step="0.1" name="rating" class="form-control" value="<?= htmlspecialchars($anime['rating'] ?? '') ?>" min="0" max="10"></div>
            <div class="form-group"><label>Age Rating</label>
                <select name="age_rating" class="form-control"><option value="">Select</option><?php foreach(['G','PG','PG-13','R','R+','Rx'] as $r): $sel = ($anime['age_rating']===$r)?'selected':''; ?><option <?=$sel?> value="<?=$r?>"><?=$r?></option><?php endforeach; ?></select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Source</label><input type="text" name="source" class="form-control" value="<?= htmlspecialchars($anime['source'] ?? '') ?>"></div>
            <div class="form-group"><label>Studio</label><input type="text" name="studio" class="form-control" value="<?= htmlspecialchars($anime['studio'] ?? '') ?>"></div>
        </div>
        <div class="form-group"><label>Thumbnail URL</label><input type="url" name="thumbnail" class="form-control" value="<?= htmlspecialchars($anime['thumbnail'] ?? '') ?>"></div>
        <div class="form-group"><label>Banner URL</label><input type="url" name="banner" class="form-control" value="<?= htmlspecialchars($anime['banner'] ?? '') ?>"></div>
        <div class="form-group">
            <label>Genres</label>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                <?php foreach ($genres as $g): $checked = in_array($g['id'], $selected_ids) ? 'checked' : ''; ?>
                <label class="form-check"><input type="checkbox" name="genres[]" value="<?=$g['id']?>" <?=$checked?>> <?= htmlspecialchars($g['name']) ?></label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="form-group"><label class="form-check"><input type="checkbox" name="featured" value="1" <?= $anime['featured']?'checked':''?>> Featured</label></div>
        <div style="display:flex;gap:8px;margin-top:16px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            <a href="anime.php" class="btn btn-secondary">Cancel</a>
            <?php if (user_can('anime.delete')): ?>
            <a href="anime.php?action=delete&id=<?=$id?>" class="btn btn-danger" data-confirm="Delete this anime and all episodes?" style="margin-left:auto;"><i class="fas fa-trash"></i> Delete</a>
            <?php endif; ?>
        </div>
    </form>
</div>
<?php
} elseif ($action === 'delete' && user_can('anime.delete')) {
    $id = (int)($_GET['id'] ?? 0);
    $anime = DB::fetch("SELECT title FROM anime WHERE id = ?", [$id]);
    if ($anime) {
        DB::execute("DELETE FROM anime WHERE id = ?", [$id]);
        log_activity('Deleted anime', 'anime', $id, ['title' => $anime['title']]);
        $_SESSION['admin_success'] = 'Anime "' . htmlspecialchars($anime['title']) . '" deleted.';
    }
    redirect(BASE_URL . '/admin/anime.php');

} else {
    require_permission('anime.view');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $per_page = 20;
    $offset = ($page - 1) * $per_page;
    $search = $_GET['search'] ?? '';
    $where = '';
    $params = [];
    if ($search) {
        $where = 'WHERE title LIKE ?';
        $params[] = '%' . $search . '%';
    }
    $total = DB::fetch("SELECT COUNT(*) as cnt FROM anime $where", $params)['cnt'];
    $anime_list = DB::fetchAll("SELECT * FROM anime $where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset", $params);
    $total_pages = ceil($total / $per_page);
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Anime (<?= $total ?>)</h3>
        <?php if (user_can('anime.create')): ?>
        <a href="anime.php?action=create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add New</a>
        <?php endif; ?>
    </div>
    <form method="get" class="search-box">
        <input type="text" name="search" class="form-control" placeholder="Search anime..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i></button>
        <?php if ($search): ?><a href="anime.php" class="btn btn-secondary">Clear</a><?php endif; ?>
    </form>
    <?php if (count($anime_list) > 0): ?>
    <div class="table-container">
        <table>
            <thead><tr><th></th><th>Title</th><th>Type</th><th>Status</th><th>Episodes</th><th>Rating</th><th>Views</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($anime_list as $a): ?>
                <tr>
                    <td><?php if ($a['thumbnail']): ?><img src="<?= htmlspecialchars($a['thumbnail']) ?>" alt="" class="table-cell-img"><?php endif; ?></td>
                    <td><a href="anime.php?action=edit&id=<?=$a['id']?>"><strong><?= htmlspecialchars($a['title']) ?></strong></a></td>
                    <td><span class="badge badge-purple"><?= htmlspecialchars($a['type'] ?? 'N/A') ?></span></td>
                    <td><span class="badge <?= $a['status']==='Currently Airing'?'badge-green':'badge-gray' ?>"><?= htmlspecialchars($a['status'] ?? 'N/A') ?></span></td>
                    <td><?= (int)$a['episodes_count'] ?></td>
                    <td><?= $a['rating'] ?: '-' ?></td>
                    <td><?= number_format($a['views']) ?></td>
                    <td class="table-cell-actions">
                        <a href="anime.php?action=edit&id=<?=$a['id']?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                        <a href="<?= BASE_URL ?>/<?= htmlspecialchars($a['slug']) ?>" class="btn btn-sm btn-secondary" target="_blank"><i class="fas fa-external-link-alt"></i></a>
                        <?php if (user_can('anime.delete')): ?>
                        <a href="anime.php?action=delete&id=<?=$a['id']?>" class="btn btn-sm btn-danger" data-confirm="Delete this anime?" onclick="return confirm('Delete this anime?')"><i class="fas fa-trash"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="anime.php?page=<?=$i?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php else: ?>
    <div class="empty-state"><i class="fas fa-film"></i><p>No anime found. <?php if (user_can('anime.create')): ?><a href="anime.php?action=create">Add your first anime</a>.<?php endif; ?></p></div>
    <?php endif; ?>
</div>
<?php
}
require_once __DIR__ . '/footer.php';
