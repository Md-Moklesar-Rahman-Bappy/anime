<?php
// ---- AJAX: Post/delete comments ----
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
$action = $_POST['action'] ?? '';

if ($action === 'post') {
    $episode_id = (int)($_POST['episode_id'] ?? 0);
    $anime_id = (int)($_POST['anime_id'] ?? 0);
    $body = trim($_POST['body'] ?? '');

    if (!$episode_id || !$anime_id || !$body) {
        echo json_encode(['error' => 'Missing fields']);
        exit;
    }
    if (mb_strlen($body) < 2 || mb_strlen($body) > 2000) {
        echo json_encode(['error' => 'Comment must be 2-2000 characters']);
        exit;
    }

    // 30-second anti-spam
    $last = DB::fetch("SELECT created_at FROM comments WHERE user_id = ? ORDER BY created_at DESC LIMIT 1", [$user_id]);
    if ($last) {
        $diff = time() - strtotime($last['created_at']);
        if ($diff < 30) {
            echo json_encode(['error' => 'Please wait ' . (30 - $diff) . ' seconds before posting again.']);
            exit;
        }
    }

    $id = DB::insert(
        "INSERT INTO comments (user_id, episode_id, anime_id, body, created_at, updated_at) VALUES (?,?,?,?,NOW(),NOW())",
        [$user_id, $episode_id, $anime_id, $body]
    );

    $user = current_user();
    echo json_encode([
        'status' => 'ok',
        'id' => $id,
        'username' => $user['username'],
        'avatar' => $user['avatar'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&background=6c5ce7&color=fff&size=40',
        'body' => htmlspecialchars($body),
        'created_at' => 'Just now'
    ]);
    exit;
}

if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $comment = DB::fetch("SELECT * FROM comments WHERE id = ?", [$id]);
    if (!$comment) {
        echo json_encode(['error' => 'Comment not found']);
        exit;
    }
    // Only owner or admin can delete
    if ($comment['user_id'] != $user_id && !is_admin()) {
        echo json_encode(['error' => 'Not authorized']);
        exit;
    }
    DB::execute("DELETE FROM comments WHERE id = ?", [$id]);
    echo json_encode(['status' => 'deleted']);
    exit;
}

if ($action === 'list') {
    $episode_id = (int)($_GET['episode_id'] ?? 0);
    $comments = DB::fetchAll(
        "SELECT c.*, u.username, u.avatar FROM comments c
         LEFT JOIN users u ON u.id = c.user_id
         WHERE c.episode_id = ? ORDER BY c.created_at DESC LIMIT 100",
        [$episode_id]
    );
    echo json_encode($comments);
    exit;
}

echo json_encode(['error' => 'Invalid action']);
