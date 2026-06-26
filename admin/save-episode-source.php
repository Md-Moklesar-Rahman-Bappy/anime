<?php
require_once __DIR__ . '/auth_check.php';
require_permission('settings.manage');
header('Content-Type: application/json');

$anime_id = (int)($_POST['anime_id'] ?? 0);
$episode_number = (int)($_POST['episode_number'] ?? 0);
$url = trim($_POST['url'] ?? '');
$language = in_array($_POST['language'] ?? '', ['sub', 'dub']) ? $_POST['language'] : 'sub';
$label = trim($_POST['label'] ?? 'Remote Source');
$source_type = in_array($_POST['source_type'] ?? '', ['direct', 'telegram', 'youtube', 'external']) ? $_POST['source_type'] : 'direct';

if (!$anime_id || !$episode_number || !$url) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Check anime exists
$anime = DB::fetch("SELECT id, title FROM anime WHERE id = ?", [$anime_id]);
if (!$anime) {
    echo json_encode(['error' => 'Anime not found']);
    exit;
}

// Find or create episode
$episode = DB::fetch("SELECT id FROM episodes WHERE anime_id = ? AND number = ?", [$anime_id, $episode_number]);
if (!$episode) {
    $ep_id = DB::insert(
        "INSERT INTO episodes (anime_id, number, title, created_at) VALUES (?, ?, ?, NOW())",
        [$anime_id, $episode_number, 'Episode ' . $episode_number]
    );
    if (!$ep_id) {
        echo json_encode(['error' => 'Failed to create episode']);
        exit;
    }
    $episode = ['id' => $ep_id];
}

// Add source
DB::insert(
    "INSERT INTO episode_sources (episode_id, language, source_type, label, url, quality) VALUES (?, ?, ?, ?, ?, 'HD')",
    [$episode['id'], $language, $source_type, $label, $url]
);

echo json_encode([
    'status' => 'ok',
    'episode_id' => $episode['id'],
    'message' => 'Source added to ' . $anime['title'] . ' Ep #' . $episode_number,
]);
