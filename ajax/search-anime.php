<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
$anime_id = (int)($_GET['anime_id'] ?? 0);

if ($anime_id) {
    $anime = DB::fetch("SELECT id, title, slug, type, thumbnail FROM anime WHERE id = ?", [$anime_id]);
    if (!$anime) {
        http_response_code(404);
        echo json_encode(['error' => 'Anime not found.']);
        exit;
    }
    $episodes = DB::fetchAll(
        "SELECT id, number, title, duration FROM episodes WHERE anime_id = ? ORDER BY number ASC",
        [$anime_id]
    );
    echo json_encode(['anime' => $anime, 'episodes' => $episodes]);
    exit;
}

if (strlen($q) < 1) {
    echo json_encode([]);
    exit;
}

$results = DB::fetchAll(
    "SELECT id, title, slug, type, thumbnail FROM anime WHERE title LIKE ? ORDER BY title LIMIT 20",
    ['%' . $q . '%']
);
echo json_encode($results);
