<?php
ob_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure required tables exist
try {
    DB::query("CREATE TABLE IF NOT EXISTS `activity_logs` (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `user_id` BIGINT UNSIGNED DEFAULT NULL,
        `username` VARCHAR(100) DEFAULT NULL,
        `action` VARCHAR(100) NOT NULL,
        `entity_type` VARCHAR(50) DEFAULT NULL,
        `entity_id` BIGINT UNSIGNED DEFAULT NULL,
        `details` TEXT DEFAULT NULL,
        `ip_address` VARCHAR(45) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY `activity_logs_user_id_foreign` (`user_id`),
        KEY `activity_logs_created_at_index` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Exception $e) {}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_URL . '/?login=1');
    exit;
}

$user = current_user();
if (!$user) {
    session_destroy();
    header('Location: ' . BASE_URL . '/?login=1');
    exit;
}

$GLOBALS['_user'] = $user;
$GLOBALS['_role_name'] = $user['role'] ?? 'user';
$GLOBALS['_role_level'] = 0;

if (!empty($user['role_id'])) {
    $role = DB::fetch("SELECT * FROM roles WHERE id = ?", [$user['role_id']]);
    if ($role) {
        $GLOBALS['_role_name'] = $role['slug'];
        $GLOBALS['_role_level'] = (int)$role['level'];
    }
}

// Fallback: if user.role is 'admin' but no role_id, give level 2 access
if ($GLOBALS['_role_level'] < 1 && ($user['role'] ?? '') === 'admin') {
    $GLOBALS['_role_level'] = 2;
}

$required_level = 1;
$user_level = $GLOBALS['_role_level'];
if ($user_level < $required_level) {
    http_response_code(403);
    require_once __DIR__ . '/layout.php';
    echo '<div class="admin-error" style="text-align:center;padding:60px 20px;"><i class="fas fa-shield-alt" style="font-size:3rem;color:var(--danger);margin-bottom:16px;"></i><h2>Access Denied</h2><p>You do not have permission to access the admin panel.</p><a href="' . BASE_URL . '" class="btn btn-primary">Back to Site</a></div>';
    require_once __DIR__ . '/footer.php';
    exit;
}

function user_can(string $permission_slug): bool {
    static $perms = null;
    $user = $GLOBALS['_user'] ?? null;
    if (!$user || empty($user['role_id'])) return false;
    if ($GLOBALS['_role_level'] >= 2) return true;
    if ($perms === null) {
        $rows = DB::fetchAll("SELECT p.slug FROM permissions p
            INNER JOIN role_permission rp ON rp.permission_id = p.id
            WHERE rp.role_id = ?", [$user['role_id']]);
        $perms = array_column($rows, 'slug');
    }
    return in_array($permission_slug, $perms, true);
}

function require_permission(string $permission_slug): void {
    if (!user_can($permission_slug)) {
        http_response_code(403);
        echo '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Permission denied: "' . htmlspecialchars($permission_slug) . '" required.</div>';
        if (isset($GLOBALS['_page_content_only'])) {
            exit;
        }
        exit;
    }
}

