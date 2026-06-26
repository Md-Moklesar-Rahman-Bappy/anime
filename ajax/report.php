<?php
// ---- AJAX: Submit episode report ----
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

if (!is_auth()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$episode_id = (int)($_POST['episode_id'] ?? 0);
$anime_id = (int)($_POST['anime_id'] ?? 0);
$type = $_POST['type'] ?? '';
$description = trim($_POST['description'] ?? '');

$valid_types = ['broken_video', 'wrong_episode', 'subtitle_issue', 'audio_issue', 'wrong_source', 'other'];
if (!in_array($type, $valid_types)) $type = 'other';

if (!$episode_id || !$anime_id) {
    echo json_encode(['error' => 'Missing episode/anime']);
    exit;
}

DB::insert(
    "INSERT INTO reports (user_id, episode_id, anime_id, type, description, status) VALUES (?,?,?,?,?,'pending')",
    [$_SESSION['user_id'], $episode_id, $anime_id, $type, $description]
);

echo json_encode(['status' => 'ok', 'message' => 'Report submitted. Thank you.']);
