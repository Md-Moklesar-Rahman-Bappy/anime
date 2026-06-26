<?php
require_once __DIR__ . '/auth_check.php';
$action = $_GET['action'] ?? 'list';
$page_title = 'Manage Episodes';
require_once __DIR__ . '/layout.php';

if ($action === 'create' && user_can('episodes.create')) {
    $page_title = 'Add Episode';
    $anime_id = (int)($_GET['anime_id'] ?? 0);
    $anime_list = DB::fetchAll("SELECT id, title FROM anime ORDER BY title");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $aid = (int)$_POST['anime_id'];
        $ep_num = (int)$_POST['number'];
        $ep_id = DB::insert(
            "INSERT INTO episodes (anime_id, number, title, description, thumbnail, duration, air_date, has_sub, has_dub) VALUES (?,?,?,?,?,?,?,?,?)",
            [
                $aid, $ep_num, $_POST['title'], $_POST['description'],
                $_POST['thumbnail'], (int)$_POST['duration'] ?: null,
                $_POST['air_date'] ?: null,
                isset($_POST['has_sub']) ? 1 : 0, isset($_POST['has_dub']) ? 1 : 0
            ]
        );
        $source_types = ['local', 'youtube', 'telegram', 'external'];
        foreach ($source_types as $st) {
            $labels = $_POST['source_label_' . $st] ?? [];
            $urls = $_POST['source_url_' . $st] ?? [];
            $qualities = $_POST['source_quality_' . $st] ?? [];
            $langs = $_POST['source_lang_' . $st] ?? [];
            $iframes = $_POST['source_iframe_' . $st] ?? [];
            $is_hls = isset($_POST['source_hls_' . $st]) && is_array($_POST['source_hls_' . $st]) ? $_POST['source_hls_' . $st] : [];
            $file_paths = $_POST['source_file_' . $st] ?? [];
            for ($i = 0; $i < count($labels); $i++) {
                if (empty($urls[$i] ?? '') && empty($iframes[$i] ?? '')) continue;
                $hls_val = isset($is_hls[$i]) ? 1 : 0;
                DB::insert(
                    "INSERT INTO episode_sources (episode_id, language, source_type, label, url, quality, embed, is_hls, is_dash, iframe_code, file_path) VALUES (?,?,?,?,?,?,?,?,?,?,?)",
                    [
                        $ep_id,
                        $langs[$i] ?? 'sub',
                        $st,
                        $labels[$i] ?: 'Server #' . ($i + 1),
                        $urls[$i] ?? '',
                        $qualities[$i] ?: 'HD',
                        $st === 'external' && !empty($iframes[$i]) ? 1 : 0,
                        $hls_val,
                        0,
                        $iframes[$i] ?? '',
                        $file_paths[$i] ?? ''
                    ]
                );
            }
        }
        if (!empty($_FILES['local_file'])) {
            handle_video_upload($_FILES['local_file'], $ep_id);
        }
        log_activity('Created episode', 'episode', $ep_id, ['anime_id' => $aid, 'number' => $ep_num]);
        $_SESSION['admin_success'] = 'Episode ' . $ep_num . ' created.';
        redirect(BASE_URL . '/admin/episodes.php?action=edit&id=' . $ep_id);
    }
?>
<div class="form-card">
    <form method="post" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-group"><label>Anime *</label>
                <select name="anime_id" class="form-control" required>
                    <option value="">Select anime...</option>
                    <?php foreach ($anime_list as $a): $sel = ($a['id']===$anime_id)?'selected':''; ?>
                    <option value="<?=$a['id']?>" <?=$sel?>><?= htmlspecialchars($a['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Episode Number *</label><input type="number" name="number" class="form-control" min="1" required></div>
        </div>
        <div class="form-group"><label>Title</label><input type="text" name="title" class="form-control" placeholder="e.g. Romance Dawn"></div>
        <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
        <div class="form-row">
            <div class="form-group"><label>Duration (seconds)</label><input type="number" name="duration" class="form-control" min="0" placeholder="1440"></div>
            <div class="form-group"><label>Air Date</label><input type="date" name="air_date" class="form-control"></div>
        </div>
        <div class="form-group"><label>Thumbnail URL</label><input type="url" name="thumbnail" class="form-control" placeholder="https://..."></div>
        <div class="form-row">
            <div class="form-group"><label class="form-check"><input type="checkbox" name="has_sub" value="1" checked> Has Subtitles</label></div>
            <div class="form-group"><label class="form-check"><input type="checkbox" name="has_dub" value="1"> Has Dub</label></div>
        </div>

        <div style="margin-top:24px;">
            <h3 style="font-size:1rem;font-weight:600;margin-bottom:12px;">Video Sources</h3>
            <div class="source-tabs">
                <button type="button" class="source-tab active" data-target="local"><i class="fas fa-upload"></i> Local Upload</button>
                <button type="button" class="source-tab" data-target="youtube"><i class="fab fa-youtube"></i> YouTube</button>
                <button type="button" class="source-tab" data-target="telegram"><i class="fab fa-telegram"></i> Telegram</button>
                <button type="button" class="source-tab" data-target="external"><i class="fas fa-link"></i> External / HLS</button>
            </div>

            <div class="source-panel active" data-panel="local">
                <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:12px;">Upload MP4, MKV, or WebM files directly to the server.</p>
                <div class="upload-area" onclick="document.getElementById('localFileInput').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Click to select a video file</p>
                    <p class="hint">MP4, MKV, WebM accepted — max 500MB</p>
                    <input type="file" id="localFileInput" name="local_file" class="file-upload-input" accept=".mp4,.mkv,.webm" style="display:none">
                    <div class="upload-preview" style="display:none;margin-top:12px;"></div>
                </div>
                <div style="margin-top:12px;">
                    <div class="form-row" style="grid-template-columns:1fr 1fr 1fr auto;">
                        <div class="form-group"><label>Label</label><input type="text" name="source_label_local[]" class="form-control" value="Server #1" placeholder="Server #1"></div>
                        <div class="form-group"><label>Quality</label><select name="source_quality_local[]" class="form-control"><option value="HD">HD</option><option value="Full HD">Full HD</option><option value="4K">4K</option><option value="SD">SD</option></select></div>
                        <div class="form-group"><label>Language</label><select name="source_lang_local[]" class="form-control"><option value="sub">Sub</option><option value="dub">Dub</option></select></div>
                        <div class="form-group" style="display:flex;align-items:flex-end;"><label class="form-check"><input type="checkbox" name="source_hls_local[]" value="1"> HLS</label></div>
                    </div>
                    <input type="hidden" name="source_url_local[]" value="">
                    <input type="hidden" name="source_iframe_local[]" value="">
                    <input type="hidden" name="source_file_local[]" value="">
                </div>
            </div>

            <div class="source-panel" data-panel="youtube">
                <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:12px;">Paste a YouTube URL or Video ID.</p>
                <div class="form-row" style="grid-template-columns:1fr 1fr auto;">
                    <div class="form-group"><label>YouTube URL / ID</label><input type="text" name="source_url_youtube[]" class="form-control youtube-url-input" placeholder="https://youtube.com/watch?v=..."></div>
                    <div class="form-group"><label>Label</label><input type="text" name="source_label_youtube[]" class="form-control" value="YouTube"></div>
                    <div class="form-group"><label>Language</label><select name="source_lang_youtube[]" class="form-control"><option value="sub">Sub</option><option value="dub">Dub</option></select></div>
                </div>
                <div class="youtube-preview" style="display:none;margin-top:8px;"></div>
                <input type="hidden" name="source_quality_youtube[]" value="HD">
                <input type="hidden" name="source_iframe_youtube[]" value="">
                <input type="hidden" name="source_hls_youtube[]" value="0">
                <input type="hidden" name="source_file_youtube[]" value="">
            </div>

            <div class="source-panel" data-panel="telegram">
                <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:12px;">Paste a direct Telegram file stream URL or CDN link.</p>
                <div class="form-row" style="grid-template-columns:2fr 1fr auto;">
                    <div class="form-group"><label>Telegram URL</label><input type="url" name="source_url_telegram[]" class="form-control" placeholder="https://..."></div>
                    <div class="form-group"><label>Label</label><input type="text" name="source_label_telegram[]" class="form-control" value="Telegram"></div>
                    <div class="form-group"><label>Language</label><select name="source_lang_telegram[]" class="form-control"><option value="sub">Sub</option><option value="dub">Dub</option></select></div>
                </div>
                <input type="hidden" name="source_quality_telegram[]" value="HD">
                <input type="hidden" name="source_iframe_telegram[]" value="">
                <input type="hidden" name="source_hls_telegram[]" value="0">
                <input type="hidden" name="source_file_telegram[]" value="">
            </div>

            <div class="source-panel" data-panel="external">
                <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:12px;">Provide an external stream URL (HLS .m3u8, DASH .mpd, or direct MP4) or an iframe embed code.</p>
                <div class="form-row" style="grid-template-columns:1fr 1fr 1fr auto;">
                    <div class="form-group"><label>Stream URL</label><input type="url" name="source_url_external[]" class="form-control" placeholder="https://..."></div>
                    <div class="form-group"><label>Label</label><input type="text" name="source_label_external[]" class="form-control" value="Server #1"></div>
                    <div class="form-group"><label>Quality</label><select name="source_quality_external[]" class="form-control"><option value="HD">HD</option><option value="Full HD">Full HD</option><option value="4K">4K</option><option value="SD">SD</option></select></div>
                    <div class="form-group"><label>Language</label><select name="source_lang_external[]" class="form-control"><option value="sub">Sub</option><option value="dub">Dub</option></select></div>
                </div>
                <div class="form-group"><label>Iframe Embed Code (alternative to URL)</label><textarea name="source_iframe_external[]" class="form-control" rows="2" placeholder="&lt;iframe src=&quot;...&quot;&gt;&lt;/iframe&gt;"></textarea></div>
                <div class="form-row" style="grid-template-columns:auto auto auto;">
                    <div class="form-group"><label class="form-check"><input type="checkbox" name="source_hls_external[]" value="1"> HLS (.m3u8)</label></div>
                </div>
                <input type="hidden" name="source_file_external[]" value="">
            </div>
        </div>

        <div style="display:flex;gap:8px;margin-top:20px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create Episode</button>
            <a href="episodes.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php
} elseif ($action === 'edit' && user_can('episodes.edit')) {
    $id = (int)($_GET['id'] ?? 0);
    $ep = DB::fetch("SELECT e.*, a.title as anime_title, a.slug as anime_slug FROM episodes e INNER JOIN anime a ON a.id = e.anime_id WHERE e.id = ?", [$id]);
    if (!$ep) { echo '<div class="alert alert-danger">Episode not found.</div>'; require __DIR__ . '/footer.php'; exit; }
    $page_title = 'Edit: ' . htmlspecialchars($ep['anime_title']) . ' Ep ' . $ep['number'];
    $sources = DB::fetchAll("SELECT * FROM episode_sources WHERE episode_id = ?", [$id]);
    $anime_list = DB::fetchAll("SELECT id, title FROM anime ORDER BY title");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        DB::execute(
            "UPDATE episodes SET anime_id=?, number=?, title=?, description=?, thumbnail=?, duration=?, air_date=?, has_sub=?, has_dub=? WHERE id=?",
            [
                (int)$_POST['anime_id'], (int)$_POST['number'], $_POST['title'], $_POST['description'],
                $_POST['thumbnail'], (int)$_POST['duration'] ?: null, $_POST['air_date'] ?: null,
                isset($_POST['has_sub'])?1:0, isset($_POST['has_dub'])?1:0, $id
            ]
        );
        DB::execute("DELETE FROM episode_sources WHERE episode_id = ?", [$id]);
        $source_types = ['local', 'youtube', 'telegram', 'external'];
        foreach ($source_types as $st) {
            $labels = $_POST['source_label_' . $st] ?? [];
            $urls = $_POST['source_url_' . $st] ?? [];
            $qualities = $_POST['source_quality_' . $st] ?? [];
            $langs = $_POST['source_lang_' . $st] ?? [];
            $iframes = $_POST['source_iframe_' . $st] ?? [];
            $is_hls_arr = $_POST['source_hls_' . $st] ?? [];
            $file_paths = $_POST['source_file_' . $st] ?? [];
            for ($i = 0; $i < max(count($labels), count($urls)); $i++) {
                $url = $urls[$i] ?? '';
                $iframe = $iframes[$i] ?? '';
                if (empty($url) && empty($iframe)) continue;
                DB::insert(
                    "INSERT INTO episode_sources (episode_id, language, source_type, label, url, quality, embed, is_hls, is_dash, iframe_code, file_path) VALUES (?,?,?,?,?,?,?,?,?,?,?)",
                    [
                        $id,
                        $langs[$i] ?? 'sub', $st,
                        $labels[$i] ?: 'Server #' . ($i + 1),
                        $url, $qualities[$i] ?: 'HD',
                        ($st === 'external' && !empty($iframe)) ? 1 : 0,
                        !empty($is_hls_arr[$i]) ? 1 : 0, 0, $iframe, $file_paths[$i] ?? ''
                    ]
                );
            }
        }
        log_activity('Updated episode', 'episode', $id, ['anime_id' => $ep['anime_id'], 'number' => $_POST['number']]);
        $_SESSION['admin_success'] = 'Episode updated.';
        redirect(BASE_URL . '/admin/episodes.php?action=edit&id=' . $id);
    }
?>
<div class="form-card">
    <form method="post">
        <div class="form-row">
            <div class="form-group"><label>Anime *</label><select name="anime_id" class="form-control" required>
                <?php foreach ($anime_list as $a): $sel = ($a['id']==$ep['anime_id'])?'selected':''; ?>
                <option value="<?=$a['id']?>" <?=$sel?>><?= htmlspecialchars($a['title']) ?></option>
                <?php endforeach; ?>
            </select></div>
            <div class="form-group"><label>Episode #</label><input type="number" name="number" class="form-control" value="<?=$ep['number']?>" min="1" required></div>
        </div>
        <div class="form-group"><label>Title</label><input type="text" name="title" class="form-control" value="<?= htmlspecialchars($ep['title'] ?? '') ?>"></div>
        <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($ep['description'] ?? '') ?></textarea></div>
        <div class="form-row">
            <div class="form-group"><label>Duration (sec)</label><input type="number" name="duration" class="form-control" value="<?=$ep['duration']?>" min="0"></div>
            <div class="form-group"><label>Air Date</label><input type="date" name="air_date" class="form-control" value="<?= htmlspecialchars($ep['air_date'] ?? '') ?>"></div>
        </div>
        <div class="form-group"><label>Thumbnail URL</label><input type="url" name="thumbnail" class="form-control" value="<?= htmlspecialchars($ep['thumbnail'] ?? '') ?>"></div>
        <div class="form-row">
            <div class="form-group"><label class="form-check"><input type="checkbox" name="has_sub" value="1" <?=$ep['has_sub']?'checked':''?>> Has Sub</label></div>
            <div class="form-group"><label class="form-check"><input type="checkbox" name="has_dub" value="1" <?=$ep['has_dub']?'checked':''?>> Has Dub</label></div>
        </div>

        <div style="margin-top:24px;">
            <h3 style="font-size:1rem;font-weight:600;margin-bottom:12px;">Video Sources</h3>
            <?php
            $source_types = ['local','youtube','telegram','external'];
            $st_labels = ['local'=>'Local Upload','youtube'=>'YouTube','telegram'=>'Telegram','external'=>'External / HLS'];
            $st_icons = ['local'=>'fa-upload','youtube'=>'fa-youtube','telegram'=>'fa-telegram','external'=>'fa-link'];
            ?>
            <div class="source-tabs">
                <?php foreach ($source_types as $i => $st): ?>
                <button type="button" class="source-tab <?=$i===0?'active':''?>" data-target="<?=$st?>"><i class="fab <?=$st_icons[$st]?>"></i> <?=$st_labels[$st]?></button>
                <?php endforeach; ?>
            </div>
            <?php foreach ($source_types as $st): ?>
            <div class="source-panel <?=$st==='local'?'active':''?>" data-panel="<?=$st?>">
                <?php
                $st_sources = array_filter($sources, function($s) use ($st) { return $s['source_type'] === $st; });
                $st_sources = array_values($st_sources);
                if (count($st_sources) === 0): ?>
                <p style="color:var(--text-muted);font-size:0.85rem;">No <?=$st_labels[$st]?> sources. Add one below.</p>
                <?php endif; ?>
                <?php for ($idx = 0; $idx < max(1, count($st_sources)); $idx++):
                    $src = $st_sources[$idx] ?? null;
                ?>
                <div class="form-row" style="grid-template-columns:1fr 1fr 1fr auto;margin-bottom:8px;">
                    <div class="form-group"><label>URL</label><input type="url" name="source_url_<?=$st?>[]" class="form-control <?=$st==='youtube'?'youtube-url-input':''?>" value="<?= htmlspecialchars($src['url'] ?? '') ?>"></div>
                    <div class="form-group"><label>Label</label><input type="text" name="source_label_<?=$st?>[]" class="form-control" value="<?= htmlspecialchars($src['label'] ?? ('Server #'.($idx+1))) ?>"></div>
                    <div class="form-group"><label>Quality</label><select name="source_quality_<?=$st?>[]" class="form-control"><option value="HD" <?=($src['quality']??'')==='HD'?'selected':''?>>HD</option><option value="Full HD" <?=($src['quality']??'')==='Full HD'?'selected':''?>>Full HD</option><option value="4K" <?=($src['quality']??'')==='4K'?'selected':''?>>4K</option><option value="SD" <?=($src['quality']??'')==='SD'?'selected':''?>>SD</option></select></div>
                    <div class="form-group"><label>Lang</label><select name="source_lang_<?=$st?>[]" class="form-control"><option value="sub" <?=($src['language']??'')==='sub'?'selected':''?>>Sub</option><option value="dub" <?=($src['language']??'')==='dub'?'selected':''?>>Dub</option></select></div>
                </div>
                <?php if ($st === 'external'): ?>
                <div class="form-group"><label>Iframe Embed Code</label><textarea name="source_iframe_<?=$st?>[]" class="form-control" rows="2"><?= htmlspecialchars($src['iframe_code'] ?? '') ?></textarea></div>
                <?php else: ?>
                <input type="hidden" name="source_iframe_<?=$st?>[]" value="">
                <?php endif; ?>
                <div class="form-row" style="grid-template-columns:auto auto;">
                    <div class="form-group"><label class="form-check"><input type="checkbox" name="source_hls_<?=$st?>[]" value="1" <?=($src['is_hls']??0)?'checked':''?>> HLS</label></div>
                </div>
                <input type="hidden" name="source_file_<?=$st?>[]" value="">
                <?php endfor; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="display:flex;gap:8px;margin-top:20px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            <a href="episodes.php" class="btn btn-secondary">Cancel</a>
            <?php if (user_can('episodes.delete')): ?>
            <a href="episodes.php?action=delete&id=<?=$id?>" class="btn btn-danger" style="margin-left:auto;" data-confirm="Delete this episode?" onclick="return confirm('Delete this episode?')"><i class="fas fa-trash"></i> Delete</a>
            <?php endif; ?>
        </div>
    </form>
</div>
<?php
} elseif ($action === 'delete' && user_can('episodes.delete')) {
    $id = (int)($_GET['id'] ?? 0);
    $ep = DB::fetch("SELECT e.id, e.number, a.title as anime_title FROM episodes e INNER JOIN anime a ON a.id=e.anime_id WHERE e.id=?", [$id]);
    if ($ep) {
        DB::execute("DELETE FROM episodes WHERE id = ?", [$id]);
        log_activity('Deleted episode', 'episode', $id, ['title' => $ep['anime_title'], 'number' => $ep['number']]);
        $_SESSION['admin_success'] = 'Episode ' . $ep['number'] . ' of ' . htmlspecialchars($ep['anime_title']) . ' deleted.';
    }
    redirect(BASE_URL . '/admin/episodes.php');

} else {
    require_permission('episodes.view');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $per_page = 25;
    $offset = ($page - 1) * $per_page;
    $search = $_GET['search'] ?? '';
    $anime_filter = (int)($_GET['anime_id'] ?? 0);
    $where = [];
    $params = [];
    if ($search) { $where[] = '(e.title LIKE ? OR a.title LIKE ?)'; $params[] = '%'.$search.'%'; $params[] = '%'.$search.'%'; }
    if ($anime_filter) { $where[] = 'e.anime_id = ?'; $params[] = $anime_filter; }
    $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $total = DB::fetch("SELECT COUNT(*) as cnt FROM episodes e INNER JOIN anime a ON a.id=e.anime_id $where_clause", $params)['cnt'];
    $eps = DB::fetchAll("SELECT e.*, a.title as anime_title, a.slug as anime_slug, a.thumbnail as anime_thumb FROM episodes e INNER JOIN anime a ON a.id=e.anime_id $where_clause ORDER BY e.created_at DESC LIMIT $per_page OFFSET $offset", $params);
    $total_pages = ceil($total / $per_page);
    $anime_list = DB::fetchAll("SELECT id, title FROM anime ORDER BY title");
?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Episodes (<?= $total ?>)</h3>
        <?php if (user_can('episodes.create')): ?>
        <a href="episodes.php?action=create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Episode</a>
        <?php endif; ?>
    </div>
    <form method="get" class="search-box">
        <input type="text" name="search" class="form-control" placeholder="Search episodes..." value="<?= htmlspecialchars($search) ?>">
        <select name="anime_id" class="form-control" style="max-width:200px;">
            <option value="">All Anime</option>
            <?php foreach ($anime_list as $a): $sel = ($a['id']===$anime_filter)?'selected':''; ?>
            <option value="<?=$a['id']?>" <?=$sel?>><?= htmlspecialchars($a['title']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i></button>
        <?php if ($search || $anime_filter): ?><a href="episodes.php" class="btn btn-secondary">Clear</a><?php endif; ?>
    </form>
    <?php if (count($eps) > 0): ?>
    <div class="table-container">
        <table>
            <thead><tr><th>Anime</th><th>Ep</th><th>Title</th><th>Duration</th><th>Sub</th><th>Dub</th><th>Views</th><th>Sources</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($eps as $e): ?>
                <?php $src_count = DB::fetch("SELECT COUNT(*) as cnt FROM episode_sources WHERE episode_id=?", [$e['id']])['cnt']; ?>
                <tr>
                    <td><a href="episodes.php?anime_id=<?=$e['anime_id']?>"><?= htmlspecialchars($e['anime_title']) ?></a></td>
                    <td><strong>#<?= (int)$e['number'] ?></strong></td>
                    <td><?= htmlspecialchars(truncate($e['title'] ?? '', 40)) ?></td>
                    <td style="color:var(--text-muted);font-size:0.78rem;"><?= $e['duration'] ? gmdate('i:s', (int)$e['duration']) : '-' ?></td>
                    <td><?= $e['has_sub'] ? '<span class="badge badge-blue">SUB</span>' : '-' ?></td>
                    <td><?= $e['has_dub'] ? '<span class="badge badge-green">DUB</span>' : '-' ?></td>
                    <td><?= number_format($e['views']) ?></td>
                    <td><span class="badge badge-purple"><?= $src_count ?></span></td>
                    <td class="table-cell-actions">
                        <a href="episodes.php?action=edit&id=<?=$e['id']?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                        <a href="<?= BASE_URL ?>/watch/<?= htmlspecialchars($e['anime_slug']) ?>?ep=<?=$e['number']?>" class="btn btn-sm btn-secondary" target="_blank"><i class="fas fa-play"></i></a>
                        <?php if (user_can('episodes.delete')): ?>
                        <a href="episodes.php?action=delete&id=<?=$e['id']?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete ep #<?=$e['number']?>?')"><i class="fas fa-trash"></i></a>
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
        <a href="episodes.php?page=<?=$i?><?= $search ? '&search='.urlencode($search) : '' ?><?= $anime_filter ? '&anime_id='.$anime_filter : '' ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php else: ?>
    <div class="empty-state"><i class="fas fa-list"></i><p>No episodes found. <?php if (user_can('episodes.create')): ?><a href="episodes.php?action=create">Add your first episode</a>.<?php endif; ?></p></div>
    <?php endif; ?>
</div>
<?php
}

function handle_video_upload($file, $episode_id) {
    if ($file['error'] !== UPLOAD_ERR_OK) return;
    $allowed = ['video/mp4', 'video/x-matroska', 'video/webm', 'video/quicktime'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['mp4','mkv','webm','mov'])) return;
    $upload_dir = __DIR__ . '/uploads/videos/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    $filename = 'ep_' . $episode_id . '_' . time() . '.' . $ext;
    $dest = $upload_dir . $filename;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        $relative_path = 'admin/uploads/videos/' . $filename;
        DB::insert(
            "INSERT INTO episode_sources (episode_id, language, source_type, label, url, quality, embed, file_path, mime_type, file_size) VALUES (?,?,?,?,?,?,?,?,?,?)",
            [$episode_id, 'sub', 'local', 'Local HD', BASE_URL . '/' . $relative_path, 'HD', 0, $relative_path, $file['type'], $file['size']]
        );
    }
}

require_once __DIR__ . '/footer.php';
