<?php
require_once __DIR__ . '/auth_check.php';
require_permission('settings.manage');
$page_title = 'Remote Sources';
require_once __DIR__ . '/layout.php';

$sources = [
    'ftp5' => [
        'label' => 'Animation Dubbed Movies (ftp5)',
        'root' => 'http://ftp5.circleftp.net/FILE/Animation%20Dubbed%20Movies/',
    ],
    'dhaka_movies_1080' => [
        'label' => 'Anime Movie (1080p)',
        'root' => 'http://172.16.50.14/DHAKA-FLIX-14/Animation%20Movies%20%281080p%29/',
    ],
    'dhaka_movies' => [
        'label' => 'Anime Movie',
        'root' => 'http://172.16.50.14/DHAKA-FLIX-14/Animation%20Movies/',
    ],
    'dhaka_tv' => [
        'label' => 'Anime TV Series',
        'root' => 'http://172.16.50.9/DHAKA-FLIX-9/Anime%20%26%20Cartoon%20TV%20Series/',
    ],
];
?>
<div class="page-header">
    <h2><i class="fas fa-globe"></i> Remote Sources</h2>
    <p style="color:var(--text-muted);font-size:0.85rem;">Browse public media servers and attach files to episodes</p>
</div>

<div class="source-tabs" style="display:flex;gap:6px;margin-bottom:16px;flex-wrap:wrap;">
    <?php foreach ($sources as $key => $src): ?>
    <button class="btn btn-sm source-tab <?= $key === 'ftp5' ? 'btn-primary' : 'btn-outline' ?>" data-source="<?= $key ?>" data-root="<?= htmlspecialchars($src['root']) ?>">
        <i class="fas fa-folder"></i> <?= htmlspecialchars($src['label']) ?>
    </button>
    <?php endforeach; ?>
    <button class="btn btn-sm btn-outline" onclick="document.getElementById('customUrlBar').style.display='flex'">
        <i class="fas fa-link"></i> Custom URL
    </button>
</div>

<div id="customUrlBar" style="display:none;gap:8px;margin-bottom:16px;align-items:center;">
    <input type="text" id="customUrlInput" class="form-control" placeholder="Enter h5ai directory URL..." style="flex:1;max-width:500px;">
    <button class="btn btn-sm btn-primary" onclick="browseCustom()"><i class="fas fa-search"></i> Browse</button>
</div>

<div id="browserContainer">
    <div class="empty-state" style="padding:60px 20px;">
        <i class="fas fa-folder-open" style="font-size:3rem;margin-bottom:16px;color:var(--accent);"></i>
        <p>Select a source to browse files</p>
    </div>
</div>

<!-- Attach Modal -->
<div class="modal" id="attachModal">
    <div class="modal-content" style="max-width:550px;">
        <span class="modal-close" onclick="closeModal('attachModal')">&times;</span>
        <h3><i class="fas fa-paperclip"></i> Attach File to Episode</h3>
        <div id="attachFileInfo" style="background:var(--bg-secondary);padding:12px;border-radius:6px;margin:12px 0;font-size:0.85rem;word-break:break-all;"></div>
        <div class="form-group">
            <label>Search Anime</label>
            <input type="text" id="attachAnimeSearch" class="form-control" placeholder="Type anime title..." oninput="searchAnimeForAttach()">
        </div>
        <div id="attachAnimeResults" style="max-height:200px;overflow-y:auto;margin-bottom:12px;"></div>
        <div class="form-group" id="attachEpisodeGroup" style="display:none;">
            <label>Episode Number</label>
            <input type="number" id="attachEpisodeNum" class="form-control" value="1" min="1">
        </div>
        <div class="form-group">
            <label>Language</label>
            <select id="attachLang" class="form-control">
                <option value="sub">Sub</option>
                <option value="dub">Dub</option>
            </select>
        </div>
        <div class="form-group">
            <label>Label</label>
            <input type="text" id="attachLabel" class="form-control" placeholder="e.g., Remote Server">
        </div>
        <button class="btn btn-primary" onclick="confirmAttach()"><i class="fas fa-check"></i> Attach</button>
    </div>
</div>

<script>
var currentSources = <?= json_encode($sources) ?>;
var attachFileUrl = '';
var attachAnimeId = 0;

function browseSource(key) {
    document.querySelectorAll('.source-tab').forEach(function(t) { t.className = 'btn btn-sm btn-outline'; });
    document.querySelector('.source-tab[data-source="' + key + '"]').className = 'btn btn-sm btn-primary';
    browseUrl(currentSources[key].root);
}

function browseCustom() {
    var url = document.getElementById('customUrlInput').value.trim();
    if (url) browseUrl(url);
}

function browseUrl(url) {
    var container = document.getElementById('browserContainer');
    container.innerHTML = '<div class="empty-state" style="padding:60px;"><i class="fas fa-spinner fa-spin" style="font-size:2rem;"></i><p>Loading...</p></div>';
    fetch(BASE_URL + '/ajax/browse-remote.php?url=' + encodeURIComponent(url))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.error) {
                container.innerHTML = '<div class="empty-state" style="padding:60px;"><i class="fas fa-exclamation-triangle" style="font-size:2rem;color:#ef4444;"></i><p>' + data.error + '</p></div>';
                return;
            }
            renderBrowser(url, data.entries || []);
        })
        .catch(function(err) {
            container.innerHTML = '<div class="empty-state" style="padding:60px;"><i class="fas fa-times-circle" style="font-size:2rem;color:#ef4444;"></i><p>Failed: ' + err.message + '</p></div>';
        });
}

function renderBrowser(currentUrl, entries) {
    var html = '<div style="margin-bottom:10px;font-size:0.82rem;color:var(--text-muted);">';
    html += '<i class="fas fa-folder-open"></i> <a href="javascript:browseUrl(\'' + encodeURIComponent(getParentUrl(currentUrl)) + '\')" style="color:var(--accent);text-decoration:none;">..</a>';
    html += ' <span>/</span> <span>' + decodeURIComponent(currentUrl.split('/').filter(Boolean).pop() || '') + '</span>';
    html += ' <span style="float:right;">' + entries.length + ' items</span>';
    html += '</div>';
    html += '<div class="card"><table><thead><tr><th>Name</th><th>Date</th><th>Size</th><th>Actions</th></tr></thead><tbody>';
    var dirs = entries.filter(function(e) { return e.is_dir; });
    var files = entries.filter(function(e) { return !e.is_dir; });
    var sorted = dirs.concat(files);
    for (var i = 0; i < sorted.length; i++) {
        var e = sorted[i];
        var icon = e.is_dir ? '<i class="fas fa-folder" style="color:var(--accent);"></i>' : '<i class="fas fa-video" style="color:#10b981;"></i>';
        var sizeStr = e.size ? formatSize(e.size_bytes || 0, e.size) : '-';
        var dateStr = e.date || '-';
        var onclick = e.is_dir ? 'browseUrl(\'' + encodeURIComponent(e.url) + '\')' : 'openAttach(\'' + encodeURIComponent(e.url) + '\', \'' + encodeURIComponent(e.name) + '\')';
        html += '<tr>';
        html += '<td><a href="javascript:void(0)" onclick="' + onclick + '" style="text-decoration:none;color:inherit;">' + icon + ' <span style="margin-left:6px;">' + htmlspecialchars(e.name) + '</span></a></td>';
        html += '<td style="font-size:0.78rem;color:var(--text-muted);white-space:nowrap;">' + dateStr + '</td>';
        html += '<td style="font-size:0.82rem;color:var(--text-muted);white-space:nowrap;">' + sizeStr + '</td>';
        html += '<td class="table-cell-actions" style="white-space:nowrap;">';
        if (!e.is_dir) {
            html += '<button class="btn btn-sm btn-primary" onclick="' + onclick + '"><i class="fas fa-paperclip"></i> Attach</button>';
        }
        html += '</td></tr>';
    }
    html += '</tbody></table></div>';
    document.getElementById('browserContainer').innerHTML = html;
}

function getParentUrl(url) {
    var parts = url.replace(/\/$/, '').split('/');
    parts.pop();
    return parts.join('/') + '/';
}

function formatSize(bytes, raw) {
    if (!bytes && bytes !== 0) return raw;
    if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(1) + ' GB';
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
    if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return bytes + ' B';
}

function openAttach(urlEncoded, nameEncoded) {
    attachFileUrl = decodeURIComponent(urlEncoded);
    var name = decodeURIComponent(nameEncoded);
    document.getElementById('attachFileInfo').innerHTML = '<strong>' + htmlspecialchars(name) + '</strong><br><span style="color:var(--text-muted);font-size:0.78rem;">' + htmlspecialchars(attachFileUrl) + '</span>';

    // Try to extract title from filename for search hint
    var title = name.replace(/\.(mkv|mp4|avi|mov|webm)$/i, '');
    title = title.replace(/\(\d{4}\).*$/, '').trim();
    title = title.replace(/\[.*?\]/g, '').trim();
    title = title.replace(/\s+\d{3,4}p\s*/i, '').trim();
    document.getElementById('attachAnimeSearch').value = title;
    document.getElementById('attachAnimeResults').innerHTML = '';
    document.getElementById('attachEpisodeGroup').style.display = 'none';
    document.getElementById('attachLabel').value = name.replace(/\.(mkv|mp4|avi|mov|webm)$/i, '').replace(/\(\d{4}\).*$/, '').trim();
    attachAnimeId = 0;
    openModal('attachModal');
    searchAnimeForAttach();
}

function searchAnimeForAttach() {
    var q = document.getElementById('attachAnimeSearch').value.trim();
    if (q.length < 2) { document.getElementById('attachAnimeResults').innerHTML = ''; return; }
    fetch(BASE_URL + '/ajax/search-anime.php?q=' + encodeURIComponent(q))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var div = document.getElementById('attachAnimeResults');
            if (!data.length) {
                div.innerHTML = '<div style="padding:8px;color:var(--text-muted);font-size:0.85rem;">No results</div>';
                return;
            }
            var html = '';
            for (var i = 0; i < data.length; i++) {
                var a = data[i];
                html += '<div class="anime-result" data-id="' + a.id + '" data-title="' + htmlspecialchars(a.title) + '" style="padding:8px 10px;cursor:pointer;border-bottom:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center;" onclick="selectAnimeForAttach(' + a.id + ',\'' + htmlspecialchars(a.title) + '\')">';
                html += '<span>' + htmlspecialchars(a.title) + ' <span style="color:var(--text-muted);font-size:0.78rem;">[' + (a.type || '?') + ']</span></span>';
                html += '<span style="color:var(--text-muted);font-size:0.78rem;">' + (a.episode_count || 0) + ' eps</span>';
                html += '</div>';
            }
            div.innerHTML = html;
        });
}

function selectAnimeForAttach(id, title) {
    attachAnimeId = id;
    document.getElementById('attachAnimeResults').innerHTML = '<div style="padding:8px;color:var(--success);font-size:0.85rem;"><i class="fas fa-check"></i> ' + htmlspecialchars(title) + '</div>';
    document.getElementById('attachEpisodeGroup').style.display = 'block';
}

function confirmAttach() {
    if (!attachAnimeId) { alert('Search and select an anime first.'); return; }
    var epNum = parseInt(document.getElementById('attachEpisodeNum').value) || 1;
    var lang = document.getElementById('attachLang').value;
    var label = document.getElementById('attachLabel').value.trim() || 'Remote Source';

    var btn = document.querySelector('#attachModal .btn-primary');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    // First create episode if needed, then add source
    fetch(BASE_URL + '/admin/save-episode-source.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'anime_id=' + attachAnimeId + '&episode_number=' + epNum + '&url=' + encodeURIComponent(attachFileUrl) + '&language=' + lang + '&label=' + encodeURIComponent(label) + '&source_type=direct'
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.error) { alert(data.error); btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Attach'; return; }
        alert('✅ Attached to episode #' + epNum + '!');
        closeModal('attachModal');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Attach';
    })
    .catch(function(err) {
        alert('Error: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Attach';
    });
}

function htmlspecialchars(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// Init: activate first tab
document.addEventListener('DOMContentLoaded', function() {
    var firstTab = document.querySelector('.source-tab');
    if (firstTab) browseSource(firstTab.dataset.source);
});
</script>
<?php require_once __DIR__ . '/footer.php'; ?>
