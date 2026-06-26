<?php
require_once __DIR__ . '/auth_check.php';
require_permission('imports.run');
$page_title = 'API Imports';
require_once __DIR__ . '/layout.php';

$source = $_GET['source'] ?? 'jikan';

$import_log = DB::fetchAll("SELECT il.*, u.username FROM import_logs il LEFT JOIN users u ON u.id=il.created_by ORDER BY il.created_at DESC LIMIT 20");

// Helper: safe Jikan API call with rate-limit retry
function jikan_call(string $path, int $retries = 3): ?array {
    $url = 'https://api.jikan.moe/v4/' . ltrim($path, '/');
    for ($i = 0; $i < $retries; $i++) {
        $ctx = stream_context_create(['http' => ['timeout' => 15, 'header' => "User-Agent: Anikoto/1.0\r\n"]]);
        $response = @file_get_contents($url, false, $ctx);
        if ($response !== false) {
            return json_decode($response, true);
        }
        // Check if rate-limited via $http_response_header
        if (isset($http_response_header[0]) && strpos($http_response_header[0], '429') !== false) {
            sleep(2);
            continue;
        }
        break;
    }
    return null;
}

// Helper: import episodes from Jikan for a given mal_id into the database
function import_episodes_from_jikan(int $mal_id, int $anime_id, string $source): int {
    $imported = 0;
    $ep_page = 1;
    while ($ep_page <= 5) {
        $ep_data = jikan_call("anime/{$mal_id}/episodes?page={$ep_page}");
        if (!$ep_data || empty($ep_data['data'])) break;
        foreach ($ep_data['data'] as $ep) {
            $ep_num = (int)$ep['mal_id'];
            $existing = DB::fetch("SELECT id FROM episodes WHERE anime_id = ? AND number = ?", [$anime_id, $ep_num]);
            if ($existing) continue;
            $ep_title = $ep['title'] ?? '';
            $ep_jp = $ep['title_japanese'] ?? '';
            $display_title = $ep_title ?: "Episode {$ep_num}";
            $ep_id = DB::insert(
                "INSERT INTO episodes (anime_id, number, title, description, thumbnail, duration, air_date, has_sub, has_dub, created_at) VALUES (?,?,?,?,?,?,?,?,?,NOW())",
                [
                    $anime_id, $ep_num, $display_title,
                    $ep['synopsis'] ?? '', $ep['images']['jpg']['image_url'] ?? '',
                    (int)$ep['duration'], $ep['aired'] ?? null,
                    1, 0
                ]
            );
            // Add a default source
            DB::insert(
                "INSERT INTO episode_sources (episode_id, language, source_type, label, is_hls) VALUES (?, 'sub', 'external', 'Server #1', 0)",
                [$ep_id]
            );
            $imported++;
        }
        $has_next = ($ep_data['pagination']['has_next_page'] ?? false);
        if (!$has_next) break;
        $ep_page++;
        usleep(500000); // 0.5s between pages
    }
    return $imported;
}
?>

<!-- Tabs -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-download"></i> Import Anime</h3>
    </div>
    <div class="tabs" style="display:flex;gap:0;border-bottom:1px solid var(--border);">
        <a href="imports.php?action=search" class="tab-btn <?= ($_GET['action'] ?? 'search') === 'search' ? 'active' : '' ?>" style="padding:10px 18px;border-radius:0;border-bottom:2px solid transparent;<?= ($_GET['action'] ?? 'search') === 'search' ? 'border-bottom-color:var(--accent);font-weight:600;' : '' ?>"><i class="fas fa-search"></i> Search</a>
        <a href="imports.php?action=batch" class="tab-btn <?= ($_GET['action'] ?? '') === 'batch' ? 'active' : '' ?>" style="padding:10px 18px;border-radius:0;border-bottom:2px solid transparent;<?= ($_GET['action'] ?? '') === 'batch' ? 'border-bottom-color:var(--accent);font-weight:600;' : '' ?>"><i class="fas fa-list"></i> Batch Import</a>
        <a href="imports.php?action=episodes" class="tab-btn <?= ($_GET['action'] ?? '') === 'episodes' ? 'active' : '' ?>" style="padding:10px 18px;border-radius:0;border-bottom:2px solid transparent;<?= ($_GET['action'] ?? '') === 'episodes' ? 'border-bottom-color:var(--accent);font-weight:600;' : '' ?>"><i class="fas fa-video"></i> Import Episodes</a>
    </div>
    <div style="padding:16px;">
        <form method="get" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <input type="hidden" name="action" value="<?= htmlspecialchars($_GET['action'] ?? 'search') ?>">
            <div class="form-group" style="margin-bottom:0;">
                <label style="font-size:0.78rem;">Source API</label>
                <select name="source" class="form-control" style="width:140px;" onchange="this.form.submit()">
                    <option value="jikan" <?=$source==='jikan'?'selected':''?>>MyAnimeList (Jikan)</option>
                    <option value="anilist" <?=$source==='anilist'?'selected':''?>>AniList</option>
                </select>
            </div>
            <?php if (($_GET['action'] ?? 'search') === 'search'): ?>
            <div class="form-group" style="margin-bottom:0;flex:1;min-width:200px;">
                <label style="font-size:0.78rem;">Search Query</label>
                <div style="display:flex;gap:4px;">
                    <input type="text" name="q" class="form-control" placeholder="Search..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <?php elseif (($_GET['action'] ?? '') === 'batch'): ?>
            <div class="form-group" style="margin-bottom:0;">
                <label style="font-size:0.78rem;">Category</label>
                <select name="category" class="form-control" style="width:150px;">
                    <option value="top" <?= ($_GET['category']??'top')==='top'?'selected':'' ?>>Top Anime</option>
                    <option value="upcoming" <?= ($_GET['category']??'')==='upcoming'?'selected':'' ?>>Upcoming</option>
                    <option value="airing" <?= ($_GET['category']??'')==='airing'?'selected':'' ?>>Currently Airing</option>
                    <option value="recent" <?= ($_GET['category']??'')==='recent'?'selected':'' ?>>Recently Added</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label style="font-size:0.78rem;">Count</label>
                <select name="limit" class="form-control" style="width:80px;">
                    <?php foreach ([10,20,25] as $l): ?>
                    <option value="<?=$l?>" <?= ((int)($_GET['limit']??20))===$l?'selected':'' ?>><?=$l?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-sync"></i> Fetch</button>
            <?php elseif (($_GET['action'] ?? '') === 'episodes'): ?>
            <div class="form-group" style="margin-bottom:0;flex:1;min-width:200px;">
                <label style="font-size:0.78rem;">Select Anime</label>
                <select name="anime_id" class="form-control" onchange="this.form.submit()">
                    <option value="">Choose...</option>
                    <?php $anime_list = DB::fetchAll("SELECT id, title, mal_id FROM anime WHERE mal_id IS NOT NULL ORDER BY title LIMIT 200"); ?>
                    <?php foreach ($anime_list as $a): $sel = ((int)($_GET['anime_id'] ?? 0) === $a['id']) ? 'selected' : ''; ?>
                    <option value="<?=$a['id']?>" <?=$sel?> data-mal="<?=$a['mal_id']?>"><?= htmlspecialchars($a['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php
$action = $_GET['action'] ?? 'search';

// ==============================
// TAB 1: SEARCH
// ==============================
if ($action === 'search') {
    $query = $_GET['q'] ?? '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    if ($query) {
        $results = api_search($source, $query, $page);
        if (!empty($results['error'])) {
            echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($results['error']) . '</div>';
        } elseif (!empty($results)) {
            log_activity('Search via ' . $source, 'import', null, ['query' => $query]);
?>
<div class="card" style="margin-top:16px;">
    <div class="card-header"><h3 class="card-title">Results for "<?= htmlspecialchars($query) ?>"</h3></div>
    <?php if (count($results) > 0): ?>
    <table><thead><tr><th></th><th>Title</th><th>Type</th><th>Score</th><th>Year</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($results as $r): ?>
        <tr>
            <td><?php if (!empty($r['image'])): ?><img src="<?= htmlspecialchars($r['image']) ?>" alt="" style="width:36px;height:50px;object-fit:cover;border-radius:4px;display:block;"><?php endif; ?></td>
            <td><strong><?= htmlspecialchars($r['title']) ?></strong><br><span style="color:var(--text-muted);font-size:0.78rem;"><?= htmlspecialchars($r['title_japanese'] ?? '') ?></span></td>
            <td><span class="badge badge-purple"><?= htmlspecialchars($r['type'] ?? '') ?></span></td>
            <td><?= $r['score'] ?: '-' ?></td>
            <td><?= $r['year'] ?: '-' ?></td>
            <td class="table-cell-actions">
                <a href="imports.php?action=preview&source=<?=$source?>&mal_id=<?=$r['id']?>&q=<?=urlencode($query)?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Preview</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody></table>
    <?php else: ?>
    <div class="empty-state"><i class="fas fa-search"></i><p>No results found.</p></div>
    <?php endif; ?>
    <?php if (count($results) >= 15): ?>
    <div class="pagination">
        <?php if ($page > 1): ?><a href="imports.php?source=<?=$source?>&action=search&q=<?=urlencode($query)?>&page=<?=$page-1?>"><i class="fas fa-chevron-left"></i> Previous</a><?php endif; ?>
        <span class="active"><?=$page?></span>
        <a href="imports.php?source=<?=$source?>&action=search&q=<?=urlencode($query)?>&page=<?=$page+1?>">Next <i class="fas fa-chevron-right"></i></a>
    </div>
    <?php endif; ?>
</div>
<?php
        }
    }
}

// ==============================
// PREVIEW & SINGLE IMPORT
// ==============================
if ($action === 'preview' && !empty($_GET['mal_id'])) {
    $mal_id = (int)$_GET['mal_id'];
    $details = api_detail($source, $mal_id);
    if (!empty($details['error'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($details['error']) . '</div>';
    } elseif ($details) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_confirm'])) {
            $slug = slugify($details['title']);
            $existing = DB::fetch("SELECT id FROM anime WHERE slug = ? OR mal_id = ?", [$slug, $details['mal_id']]);
            if ($existing) {
                echo '<div class="alert alert-warning">This anime already exists. <a href="anime.php?action=edit&id=' . $existing['id'] . '">Edit it</a>.</div>';
            } else {
                $anime_id = DB::insert(
                    "INSERT INTO anime (mal_id, title, title_japanese, slug, description, type, status, year, rating, age_rating, score, episodes_count, duration, source, studio, producers, licensors, thumbnail, banner) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                    [
                        $details['mal_id'], $details['title'], $details['title_japanese'],
                        $slug, $details['description'], $details['type'], $details['status'],
                        $details['year'], $details['rating'] ?: null, $details['age_rating'],
                        $details['score'], (int)$details['episodes'], (int)$details['duration'],
                        $details['source'], $details['studio'], $details['producers'],
                        $details['licensors'], $details['image'], $details['banner']
                    ]
                );
                // Import genres
                $genre_ids = [];
                foreach ($details['genres'] as $gname) {
                    $gslug = slugify($gname);
                    $genre = DB::fetch("SELECT id FROM genres WHERE slug = ?", [$gslug]);
                    if (!$genre) {
                        $gid = DB::insert("INSERT INTO genres (name, slug) VALUES (?,?)", [$gname, $gslug]);
                    } else {
                        $gid = $genre['id'];
                    }
                    $genre_ids[] = $gid;
                }
                foreach ($genre_ids as $gid) {
                    DB::execute("INSERT IGNORE INTO anime_genre (anime_id, genre_id) VALUES (?,?)", [$anime_id, $gid]);
                }

                // Auto-import episodes
                $ep_count = 0;
                if ($source === 'jikan' && !empty($details['mal_id'])) {
                    $ep_count = import_episodes_from_jikan($details['mal_id'], $anime_id, $source);
                }

                DB::insert("INSERT INTO import_logs (source, action, mal_id, anime_id, payload, status, created_by) VALUES (?,?,?,?,?,?,?)",
                    [$source, 'import', $details['mal_id'], $anime_id, json_encode($details), 'approved', $GLOBALS['_user']['id'] ?? null]
                );
                log_activity('Imported anime from ' . $source, 'anime', $anime_id, ['title' => $details['title'], 'mal_id' => $details['mal_id']]);
                $msg = 'Anime "' . htmlspecialchars($details['title']) . '" imported successfully.';
                if ($ep_count > 0) $msg .= " {$ep_count} episodes also imported.";
                $_SESSION['admin_success'] = $msg;
                redirect(BASE_URL . '/admin/anime.php?action=edit&id=' . $anime_id);
            }
        }
?>
<div class="card" style="margin-top:16px;">
    <div class="card-header"><h3 class="card-title">Preview: <?= htmlspecialchars($details['title']) ?></h3></div>
    <div class="preview-grid" style="display:flex;gap:20px;">
        <div><?php if ($details['image']): ?><img src="<?= htmlspecialchars($details['image']) ?>" alt="" style="width:200px;border-radius:8px;"><?php endif; ?></div>
        <div class="preview-info" style="flex:1;">
            <h2><?= htmlspecialchars($details['title']) ?></h2>
            <?php if ($details['title_japanese']): ?><p style="color:var(--text-muted);"><?= htmlspecialchars($details['title_japanese']) ?></p><?php endif; ?>
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin:12px 0;">
                <span class="badge badge-purple"><?= htmlspecialchars($details['type'] ?? 'N/A') ?></span>
                <span class="badge badge-green"><?= htmlspecialchars($details['status'] ?? 'N/A') ?></span>
                <?php if ($details['score']): ?><span class="badge badge-orange">Score: <?= $details['score'] ?></span><?php endif; ?>
                <?php if ($details['episodes']): ?><span class="badge badge-blue"><?= $details['episodes'] ?> eps</span><?php endif; ?>
                <?php if ($details['year']): ?><span class="badge badge-gray"><?= $details['year'] ?></span><?php endif; ?>
            </div>
            <p><?= htmlspecialchars(truncate(strip_tags($details['description'] ?? ''), 500)) ?></p>
            <div><?php foreach ($details['genres'] as $g): ?><span class="badge badge-purple"><?= htmlspecialchars($g) ?></span> <?php endforeach; ?></div>
            <?php if ($details['studio']): ?><p style="margin-top:8px;font-size:0.85rem;"><strong>Studio:</strong> <?= htmlspecialchars($details['studio']) ?></p><?php endif; ?>
            <p style="font-size:0.85rem;color:var(--text-muted);"><i class="fas fa-info-circle"></i> Episodes will be auto-imported from the API.</p>
            <form method="post" style="margin-top:16px;">
                <input type="hidden" name="import_confirm" value="1">
                <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Import to Database</button>
                <a href="imports.php?source=<?=$source?>&action=search&q=<?=urlencode($_GET['q'] ?? '')?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?php
    }
}

// ==============================
// TAB 2: BATCH IMPORT
// ==============================
if ($action === 'batch'):
    $category = $_GET['category'] ?? 'top';
    $limit = min(25, max(1, (int)($_GET['limit'] ?? 20)));
?>
<div class="card" style="margin-top:16px;">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list"></i> Batch Import — <?= ucfirst($category) ?> Anime</h3>
    </div>
    <?php
    // Build Jikan endpoint
    $api_path = '';
    switch ($category) {
        case 'top': $api_path = "top/anime?limit={$limit}"; break;
        case 'upcoming': $api_path = "seasons/upcoming?limit={$limit}&sfw"; break;
        case 'airing': $api_path = "seasons/now?limit={$limit}&sfw"; break;
        case 'recent': $api_path = "anime?order_by=start_date&sort=desc&limit={$limit}&sfw"; break;
    }
    $data = jikan_call($api_path);
    $items = $data['data'] ?? [];

    if (!$data) {
        echo '<div class="alert alert-danger">Failed to connect to Jikan API. Rate-limited or unavailable.</div>';
    } elseif (empty($items)) {
        echo '<div class="empty-state"><i class="fas fa-inbox"></i><p>No anime found.</p></div>';
    } else {
        // Handle batch POST import
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batch_import'])) {
            $selected = $_POST['selected'] ?? [];
            $import_eps = isset($_POST['import_episodes']);
            $imported_count = 0;
            $ep_total = 0;
            foreach ($selected as $mal_id) {
                $mal_id = (int)$mal_id;
                if ($mal_id <= 0) continue;
                // Check if already exists
                $existing = DB::fetch("SELECT id FROM anime WHERE mal_id = ?", [$mal_id]);
                if ($existing) continue;
                // Fetch full details
                $detail = api_detail($source, $mal_id);
                if (empty($detail['mal_id'])) continue;
                $slug = slugify($detail['title']);
                $anime_id = DB::insert(
                    "INSERT INTO anime (mal_id, title, title_japanese, slug, description, type, status, year, rating, age_rating, score, episodes_count, duration, source, studio, producers, licensors, thumbnail, banner) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                    [
                        $detail['mal_id'], $detail['title'], $detail['title_japanese'],
                        $slug, $detail['description'], $detail['type'], $detail['status'],
                        $detail['year'], $detail['rating'] ?: null, $detail['age_rating'],
                        $detail['score'], max(0, (int)$detail['episodes']), (int)$detail['duration'],
                        $detail['source'], $detail['studio'], $detail['producers'],
                        $detail['licensors'], $detail['image'], $detail['banner']
                    ]
                );
                // Genres
                $genre_ids = [];
                foreach ($detail['genres'] as $gname) {
                    $gslug = slugify($gname);
                    $genre = DB::fetch("SELECT id FROM genres WHERE slug = ?", [$gslug]);
                    $genre_ids[] = $genre ? $genre['id'] : DB::insert("INSERT INTO genres (name, slug) VALUES (?,?)", [$gname, $gslug]);
                }
                foreach ($genre_ids as $gid) {
                    DB::execute("INSERT IGNORE INTO anime_genre (anime_id, genre_id) VALUES (?,?)", [$anime_id, $gid]);
                }
                // Episodes
                if ($import_eps && $detail['mal_id']) {
                    $eps = import_episodes_from_jikan($detail['mal_id'], $anime_id, $source);
                    $ep_total += $eps;
                }
                DB::insert("INSERT INTO import_logs (source, action, mal_id, anime_id, payload, status, created_by) VALUES (?,?,?,?,?,?,?)",
                    [$source, 'batch_import', $detail['mal_id'], $anime_id, json_encode($detail), 'approved', $GLOBALS['_user']['id'] ?? null]
                );
                $imported_count++;
                usleep(400000); // 0.4s between imports to respect rate limits
            }
            $msg = "Imported {$imported_count} anime.";
            if ($import_eps) $msg .= " {$ep_total} total episodes imported.";
            $_SESSION['admin_success'] = $msg;
            log_activity('Batch import', 'import', null, ['source' => $source, 'category' => $category, 'count' => $imported_count]);
            redirect(BASE_URL . '/admin/imports.php?action=batch&source=' . $source . '&category=' . $category . '&limit=' . $limit);
        }
    ?>
    <form method="post" id="batchForm">
        <input type="hidden" name="batch_import" value="1">
        <div style="display:flex;gap:10px;align-items:center;padding:12px 16px;border-bottom:1px solid var(--border);flex-wrap:wrap;">
            <label style="font-size:13px;"><input type="checkbox" id="selectAll"> Select All</label>
            <label style="font-size:13px;"><input type="checkbox" name="import_episodes" value="1" checked> Import Episodes</label>
            <span style="color:var(--text-muted);font-size:12px;"><?= count($items) ?> results</span>
            <button type="submit" class="btn btn-success" style="margin-left:auto;"><i class="fas fa-download"></i> Import Selected</button>
        </div>
        <table><thead><tr><th style="width:32px;"></th><th></th><th>Title</th><th>Type</th><th>Score</th><th>Episodes</th><th>Status</th></tr></thead><tbody>
            <?php foreach ($items as $item):
                $mid = $item['mal_id'] ?? $item['id'] ?? 0;
                $exists = DB::fetch("SELECT id FROM anime WHERE mal_id = ?", [$mid]);
            ?>
            <tr>
                <td><input type="checkbox" name="selected[]" value="<?= $mid ?>" class="batch-check" <?= $exists ? 'disabled' : '' ?>></td>
                <td><?php $img = $item['images']['jpg']['image_url'] ?? $item['images']['webp']['image_url'] ?? ''; if ($img): ?><img src="<?= htmlspecialchars($img) ?>" alt="" style="width:36px;height:50px;object-fit:cover;border-radius:4px;"><?php endif; ?></td>
                <td><strong><?= htmlspecialchars($item['title']) ?></strong><?php if ($exists): ?><br><span style="color:var(--success);font-size:11px;"><i class="fas fa-check"></i> Already imported</span><?php endif; ?></td>
                <td><span class="badge badge-purple"><?= htmlspecialchars($item['type'] ?? '') ?></span></td>
                <td><?= $item['score'] ?? '-' ?></td>
                <td><?= $item['episodes'] ?? '?' ?></td>
                <td><span class="badge <?= ($item['status'] ?? '') === 'Currently Airing' ? 'badge-green' : 'badge-gray' ?>"><?= htmlspecialchars($item['status'] ?? '') ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody></table>
    </form>
    <script>
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.batch-check:not(:disabled)').forEach(cb => cb.checked = this.checked);
    });
    </script>
    <?php } ?>
</div>
<?php endif; ?>

<?php
// ==============================
// TAB 3: IMPORT EPISODES (for existing anime)
// ==============================
if ($action === 'episodes'):
    $anime_id = (int)($_GET['anime_id'] ?? 0);
    if ($anime_id):
        $anime = DB::fetch("SELECT * FROM anime WHERE id = ?", [$anime_id]);
        if (!$anime):
            echo '<div class="alert alert-danger">Anime not found.</div>';
        elseif (empty($anime['mal_id'])):
            echo '<div class="alert alert-warning">This anime has no MAL ID. Import episodes by MAL ID is not available.</div>';
        else:
            $existing_eps = DB::fetch("SELECT COUNT(*) as cnt FROM episodes WHERE anime_id = ?", [$anime_id])['cnt'];
?>
<div class="card" style="margin-top:16px;">
    <div class="card-header">
        <h3 class="card-title">Import Episodes: <?= htmlspecialchars($anime['title']) ?></h3>
    </div>
    <div style="padding:16px;">
        <p>MAL ID: <strong><?= $anime['mal_id'] ?></strong> &middot; Existing episodes: <strong><?= $existing_eps ?></strong> &middot; Expected total: <strong><?= (int)$anime['episodes_count'] ?: '?' ?></strong></p>
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['do_import_eps'])): ?>
            <?php
            $imported = import_episodes_from_jikan((int)$anime['mal_id'], $anime_id, $source);
            $msg = "Imported {$imported} new episodes.";
            $_SESSION['admin_success'] = $msg;
            log_activity('Imported episodes', 'episode', $anime_id, ['mal_id' => $anime['mal_id'], 'count' => $imported]);
            redirect(BASE_URL . '/admin/imports.php?action=episodes&source=' . $source . '&anime_id=' . $anime_id);
            ?>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="do_import_eps" value="1">
            <button type="submit" class="btn btn-primary"><i class="fas fa-cloud-download-alt"></i> Fetch & Import Episodes</button>
            <a href="episodes.php?anime_id=<?= $anime_id ?>" class="btn btn-info"><i class="fas fa-list"></i> View Episodes</a>
        </form>
    </div>
</div>
<?php
        endif;
    else:
        echo '<div class="empty-state" style="margin-top:16px;"><i class="fas fa-hand-pointer"></i><p>Select an anime above to import its episodes.</p></div>';
    endif;
endif;
?>

<!-- Import History -->
<div class="card" style="margin-top:20px;">
    <div class="card-header"><h3 class="card-title">Import History</h3></div>
    <?php if (count($import_log) > 0): ?>
    <table><thead><tr><th>Source</th><th>Action</th><th>Status</th><th>User</th><th>Date</th></tr></thead><tbody>
        <?php foreach ($import_log as $log): ?>
        <tr>
            <td><span class="badge badge-blue"><?= htmlspecialchars($log['source']) ?></span></td>
            <td><?= htmlspecialchars($log['action'] ?: '-') ?></td>
            <td><span class="badge <?= $log['status']==='approved'?'badge-green':'badge-orange' ?>"><?= $log['status'] ?></span></td>
            <td><?= htmlspecialchars($log['username'] ?? '-') ?></td>
            <td style="color:var(--text-muted);font-size:0.78rem;"><?= time_ago($log['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody></table>
    <?php else: ?>
    <div class="empty-state"><i class="fas fa-history"></i><p>No imports yet.</p></div>
    <?php endif; ?>
</div>

<?php

// ---- API FUNCTIONS ----

function api_search($source, $query, $page = 1) {
    if ($source === 'jikan') {
        $data = jikan_call('anime?q=' . urlencode($query) . '&page=' . $page . '&limit=15&sfw');
        if (!$data) return ['error' => 'Failed to connect to Jikan API. The API may be rate-limited. Try again in a few seconds.'];
        if (empty($data['data'])) return [];
        $results = [];
        foreach ($data['data'] as $item) {
            $results[] = [
                'id' => $item['mal_id'],
                'title' => $item['title'],
                'title_japanese' => $item['title_japanese'] ?? '',
                'type' => $item['type'] ?? '',
                'score' => $item['score'] ?? '',
                'year' => $item['year'] ?? ($item['aired']['from'] ? date('Y', strtotime($item['aired']['from'])) : ''),
                'image' => $item['images']['jpg']['image_url'] ?? ($item['images']['webp']['image_url'] ?? ''),
            ];
        }
        return $results;
    } elseif ($source === 'anilist') {
        $graphql = json_encode(['query' => '
            query ($search: String, $page: Int) {
                Page(page: $page, perPage: 15) {
                    media(search: $search, type: ANIME, sort: SEARCH_MATCH) {
                        id title { romaji native english }
                        type format status seasonYear averageScore episodes duration genres studios { nodes { name } }
                        coverImage { large } description
                    }
                }
            }', 'variables' => ['search' => $query, 'page' => $page]]);
        $ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => "Content-Type: application/json\r\nUser-Agent: Anikoto/1.0\r\n", 'content' => $graphql, 'timeout' => 15]]);
        $response = @file_get_contents('https://graphql.anilist.co', false, $ctx);
        if (!$response) return ['error' => 'Failed to connect to AniList API.'];
        $data = json_decode($response, true);
        if (empty($data['data']['Page']['media'])) return [];
        $results = [];
        foreach ($data['data']['Page']['media'] as $item) {
            $results[] = [
                'id' => $item['id'],
                'title' => $item['title']['romaji'] ?? ($item['title']['english'] ?? ''),
                'title_japanese' => $item['title']['native'] ?? '',
                'type' => $item['format'] ?? '',
                'score' => $item['averageScore'] ? ($item['averageScore'] / 10) : '',
                'year' => $item['seasonYear'] ?? '',
                'image' => $item['coverImage']['large'] ?? '',
            ];
        }
        return $results;
    }
    return ['error' => 'Unknown source.'];
}

function api_detail($source, $id) {
    if ($source === 'jikan') {
        $data = jikan_call('anime/' . $id . '/full');
        if (!$data) return ['error' => 'Failed to fetch details from Jikan.'];
        if (empty($data['data'])) return ['error' => 'Anime not found.'];
        $item = $data['data'];
        $genres = [];
        foreach (($item['genres'] ?? []) as $g) { $genres[] = $g['name']; }
        return [
            'mal_id' => $item['mal_id'],
            'title' => $item['title'],
            'title_japanese' => $item['title_japanese'] ?? '',
            'description' => $item['synopsis'] ?? '',
            'type' => $item['type'] ?? '',
            'status' => $item['status'] ? str_replace(['_','-'], ' ', ucwords(strtolower($item['status']), '_')) : '',
            'year' => $item['year'] ?? ($item['aired']['from'] ? date('Y', strtotime($item['aired']['from'])) : ''),
            'rating' => $item['rating'] ?? '',
            'age_rating' => $item['rating'] ?? '',
            'score' => $item['score'] ?? '',
            'episodes' => $item['episodes'] ?? 0,
            'duration' => $item['duration'] ? (int)preg_replace('/[^0-9]/', '', explode(' ', $item['duration'])[0]) : 0,
            'source' => $item['source'] ?? '',
            'studio' => !empty($item['studios'][0]['name']) ? $item['studios'][0]['name'] : '',
            'producers' => implode(', ', array_column($item['producers'] ?? [], 'name')),
            'licensors' => implode(', ', array_column($item['licensors'] ?? [], 'name')),
            'image' => $item['images']['jpg']['large_image_url'] ?? ($item['images']['webp']['large_image_url'] ?? ''),
            'banner' => $item['images']['webp']['image_url'] ?? '',
            'genres' => $genres,
        ];
    } elseif ($source === 'anilist') {
        $graphql = json_encode(['query' => '
            query ($id: Int) {
                Media(id: $id, type: ANIME) {
                    id title { romaji native english }
                    format status seasonYear averageScore episodes duration
                    genres studios { nodes { name } }
                    coverImage { large extraLarge }
                    bannerImage description source
                    synonyms startDate { year }
                }
            }', 'variables' => ['id' => $id]]);
        $ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => "Content-Type: application/json\r\nUser-Agent: Anikoto/1.0\r\n", 'content' => $graphql, 'timeout' => 15]]);
        $response = @file_get_contents('https://graphql.anilist.co', false, $ctx);
        if (!$response) return ['error' => 'Failed to fetch details from AniList.'];
        $data = json_decode($response, true);
        if (empty($data['data']['Media'])) return ['error' => 'Anime not found.'];
        $item = $data['data']['Media'];
        $studio_nodes = $item['studios']['nodes'] ?? [];
        $studio = !empty($studio_nodes) ? $studio_nodes[0]['name'] : '';
        return [
            'mal_id' => $id,
            'title' => $item['title']['romaji'] ?? ($item['title']['english'] ?? ''),
            'title_japanese' => $item['title']['native'] ?? '',
            'description' => strip_tags($item['description'] ?? ''),
            'type' => $item['format'] ?? '',
            'status' => $item['status'] ? str_replace(['_','-'], ' ', ucwords(strtolower($item['status']), '_')) : '',
            'year' => $item['seasonYear'] ?? ($item['startDate']['year'] ?? ''),
            'rating' => '',
            'age_rating' => '',
            'score' => $item['averageScore'] ? ($item['averageScore'] / 10) : '',
            'episodes' => $item['episodes'] ?? 0,
            'duration' => $item['duration'] ?? 0,
            'source' => $item['source'] ?? '',
            'studio' => $studio,
            'producers' => '',
            'licensors' => '',
            'image' => $item['coverImage']['extraLarge'] ?? ($item['coverImage']['large'] ?? ''),
            'banner' => $item['bannerImage'] ?? '',
            'genres' => $item['genres'] ?? [],
        ];
    }
    return ['error' => 'Unknown source.'];
}

require_once __DIR__ . '/footer.php';
