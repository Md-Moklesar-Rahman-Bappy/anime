<?php
$user = $GLOBALS['_user'] ?? current_user();
$page_title = $page_title ?? 'Dashboard';
$current_page = basename($_SERVER['SCRIPT_NAME']);
$role_name = $GLOBALS['_role_name'] ?? 'user';
$role_level = $GLOBALS['_role_level'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> &mdash; Admin &mdash; <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/admin/css/admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>const BASE_URL = '<?= BASE_URL ?>';</script>
</head>
<body>
<div class="admin-wrapper">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-header">
            <a href="<?= BASE_URL ?>/admin/index.php" class="admin-logo">
                <i class="fas fa-crown"></i> <span>Anikoto Admin</span>
            </a>
            <button class="sidebar-close" id="sidebarClose"><i class="fas fa-times"></i></button>
        </div>
        <nav class="admin-nav">
            <div class="nav-section-label">Main</div>
            <a href="<?= BASE_URL ?>/admin/index.php" class="nav-item <?= $current_page === 'index.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-pie"></i><span>Dashboard</span>
            </a>
            <div class="nav-section-label">Content</div>
            <a href="<?= BASE_URL ?>/admin/anime.php" class="nav-item <?= $current_page === 'anime.php' ? 'active' : '' ?>">
                <i class="fas fa-film"></i><span>Anime</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/episodes.php" class="nav-item <?= $current_page === 'episodes.php' ? 'active' : '' ?>">
                <i class="fas fa-list"></i><span>Episodes</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/genres.php" class="nav-item <?= $current_page === 'genres.php' ? 'active' : '' ?>">
                <i class="fas fa-tags"></i><span>Genres</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/comments.php" class="nav-item <?= $current_page === 'comments.php' ? 'active' : '' ?>">
                <i class="fas fa-comments"></i><span>Comments</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/reports.php" class="nav-item <?= $current_page === 'reports.php' ? 'active' : '' ?>">
                <i class="fas fa-flag"></i><span>Reports</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/requests.php" class="nav-item <?= $current_page === 'requests.php' ? 'active' : '' ?>">
                <i class="fas fa-plus-circle"></i><span>Requests</span>
            </a>
            <div class="nav-section-label">Ingestion</div>
            <a href="<?= BASE_URL ?>/admin/imports.php" class="nav-item <?= $current_page === 'imports.php' ? 'active' : '' ?>">
                <i class="fas fa-download"></i><span>API Imports</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/telegram.php" class="nav-item <?= $current_page === 'telegram.php' ? 'active' : '' ?>">
                <i class="fab fa-telegram"></i><span>Telegram Bot</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/telegram-videos.php" class="nav-item <?= $current_page === 'telegram-videos.php' ? 'active' : '' ?>">
                <i class="fas fa-video"></i><span>Telegram Videos</span>
            </a>
            <div class="nav-section-label">System</div>
            <?php if (user_can('users.view')): ?>
            <a href="<?= BASE_URL ?>/admin/users.php" class="nav-item <?= $current_page === 'users.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i><span>Users</span>
            </a>
            <?php endif; ?>
            <?php if (user_can('settings.manage')): ?>
            <a href="<?= BASE_URL ?>/admin/settings.php" class="nav-item <?= $current_page === 'settings.php' ? 'active' : '' ?>">
                <i class="fas fa-cog"></i><span>Settings</span>
            </a>
            <?php endif; ?>
            <div class="nav-section-label">Site</div>
            <a href="<?= BASE_URL ?>" class="nav-item" target="_blank">
                <i class="fas fa-external-link-alt"></i><span>View Site</span>
            </a>
            <form method="post" action="<?= BASE_URL ?>/auth/logout" style="display:inline">
                <button type="submit" class="nav-item nav-logout" style="background:none;border:none;width:100%;cursor:pointer;color:inherit;font:inherit;display:flex;align-items:center;gap:12px;padding:10px 20px;font-size:0.9rem;font-weight:500">
                    <i class="fas fa-sign-out-alt"></i><span>Logout</span>
                </button>
            </form>
        </nav>
    </aside>
    <main class="admin-main">
        <header class="admin-topbar">
            <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <div class="topbar-title"><h1><?= htmlspecialchars($page_title) ?></h1></div>
            <div class="topbar-right">
                <a href="<?= BASE_URL ?>/admin/comments.php" class="topbar-badge" title="Comments"><i class="fas fa-comments"></i></a>
                <div class="topbar-user">
                    <img src="<?= $user['avatar'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&background=6c5ce7&color=fff' ?>" alt="Avatar" class="user-avatar-sm">
                    <span class="user-name"><?= htmlspecialchars($user['username']) ?></span>
                    <span class="user-role-badge role-<?= $role_name ?>"><?= ucfirst($role_name) ?></span>
                </div>
            </div>
        </header>
        <div class="admin-content">
