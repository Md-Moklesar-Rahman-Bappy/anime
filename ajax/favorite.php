<?php
// ---- AJAX: Toggle favorite / get list status ----
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
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$anime_id = (int)($_GET['anime_id'] ?? ($_POST['anime_id'] ?? 0));

if ($action === 'toggle' && $anime_id) {
    $list_type = $_POST['list_type'] ?? 'watching';
    $valid = ['watching', 'completed', 'plan_to_watch', 'on_hold', 'dropped'];
    if (!in_array($list_type, $valid)) $list_type = 'watching';

    $existing = DB::fetch("SELECT id FROM favorites WHERE user_id = ? AND anime_id = ?", [$user_id, $anime_id]);
    if ($existing) {
        DB::execute("UPDATE favorites SET list_type = ? WHERE id = ?", [$list_type, $existing['id']]);
        echo json_encode(['status' => 'updated', 'list_type' => $list_type]);
    } else {
        DB::insert("INSERT INTO favorites (user_id, anime_id, list_type) VALUES (?,?,?)", [$user_id, $anime_id, $list_type]);
        echo json_encode(['status' => 'added', 'list_type' => $list_type]);
    }
    exit;
}

if ($action === 'remove' && $anime_id) {
    DB::execute("DELETE FROM favorites WHERE user_id = ? AND anime_id = ?", [$user_id, $anime_id]);
    echo json_encode(['status' => 'removed']);
    exit;
}

if ($action === 'status' && $anime_id) {
    $fav = DB::fetch("SELECT * FROM favorites WHERE user_id = ? AND anime_id = ?", [$user_id, $anime_id]);
    echo json_encode($fav ?: ['list_type' => null]);
    exit;
}

echo json_encode(['error' => 'Invalid request']);
