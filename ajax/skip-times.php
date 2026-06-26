<?php
// ---- AJAX: Get skip times for an episode ----
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

$episode_id = (int)($_GET['episode_id'] ?? 0);
if (!$episode_id) {
    echo json_encode([]);
    exit;
}

$skips = DB::fetchAll("SELECT type, start, `end` FROM skip_times WHERE episode_id = ? ORDER BY start ASC", [$episode_id]);
echo json_encode($skips);
