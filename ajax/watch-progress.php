<?php
// ---- AJAX: Save watch progress ----
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

if (!is_auth()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$episode_id = (int)($_POST['episode_id'] ?? 0);
$progress = (int)($_POST['progress'] ?? 0); // 0-100

if (!$episode_id) {
    echo json_encode(['error' => 'Invalid episode']);
    exit;
}

$ep = DB::fetch("SELECT e.*, e.anime_id FROM episodes e WHERE e.id = ?", [$episode_id]);
if (!$ep) {
    echo json_encode(['error' => 'Episode not found']);
    exit;
}

$existing = DB::fetch("SELECT id FROM watch_history WHERE user_id = ? AND episode_id = ?", [$user_id, $episode_id]);
$completed = ($progress >= 90) ? 1 : 0;

if ($existing) {
    DB::execute("UPDATE watch_history SET progress = ?, completed = ?, watched_at = NOW() WHERE id = ?",
        [$progress, $completed, $existing['id']]);
} else {
    DB::insert("INSERT INTO watch_history (user_id, episode_id, progress, completed, watched_at) VALUES (?,?,?,?,NOW())",
        [$user_id, $episode_id, $progress, $completed]);
}

echo json_encode(['status' => 'ok', 'progress' => $progress, 'completed' => $completed]);
