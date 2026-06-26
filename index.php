<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Simple router
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base    = parse_url(BASE_URL, PHP_URL_PATH);
$route   = substr($request, strlen($base));
$route   = '/' . trim($route ?? '', '/');
$method  = $_SERVER['REQUEST_METHOD'];

// Parse query params
$ep  = $_GET['ep'] ?? null;
$q   = $_GET['q'] ?? null;
$az  = $_GET['az'] ?? null;
$tab = $_GET['tab'] ?? 'all';
$page = max(1, (int)($_GET['page'] ?? 1));

// ---- AUTH ROUTES ----
if ($route === '/auth/logout') {
    auth_logout();
    redirect('');
}
if ($route === '/auth/reset' && $method === 'POST') {
    $email = $_POST['email'] ?? '';
    $user = DB::fetch("SELECT id FROM users WHERE email = ?", [$email]);
    if ($user) {
        $token = bin2hex(random_bytes(32));
        DB::execute("DELETE FROM password_resets WHERE email = ?", [$email]);
        DB::insert("INSERT INTO password_resets (email, token) VALUES (?, ?)", [$email, $token]);
    }
    $_SESSION['message'] = 'If that email exists, a reset link has been sent.';
    redirect('');
}
if ($method === 'POST') {
    if ($route === '/auth/login') {
        $user = auth_login($_POST['email'] ?? '', $_POST['password'] ?? '');
        if ($user) { redirect(''); }
        else { $_SESSION['error'] = 'Invalid email or password'; redirect(''); }
    }
    if ($route === '/auth/register') {
        $user = auth_register($_POST['username'] ?? '', $_POST['email'] ?? '', $_POST['password'] ?? '');
        if ($user) { redirect(''); }
        else { $_SESSION['error'] = 'Email or username already exists'; redirect(''); }
    }
    if ($route === '/contact') {
        $name = substr($_POST['name'] ?? '', 0, 100);
        $email = substr($_POST['email'] ?? '', 0, 255);
        $message = substr($_POST['message'] ?? '', 0, 5000);
        if ($name && $email && $message) {
            DB::insert("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)", [$name, $email, $message]);
            $_SESSION['message'] = 'Your message has been sent. We will get back to you soon.';
        } else {
            $_SESSION['error'] = 'All fields are required.';
        }
        redirect('contact');
    }
}

// ---- PAGE ROUTING ----
$pageFile = null;
$pageTitle = SITE_NAME;

switch (true) {
    // Home
    case $route === '/' || $route === '':
        $pageFile = __DIR__ . '/pages/home.php';
        $pageTitle = SITE_NAME . ' - Watch Anime Online';
        break;

    // Anime detail
    case preg_match('#^/([a-z0-9\-]+)$#', $route, $m) && ($_ANIME = DB::fetch("SELECT * FROM anime WHERE slug = ?", [$m[1]])):
        $_GET['slug'] = $m[1];
        $pageFile = __DIR__ . '/pages/anime-detail.php';
        $pageTitle = ($_ANIME['title'] ?? 'Anime') . ' - ' . SITE_NAME;
        break;

    // Watch
    case preg_match('#^/watch/([a-z0-9\-]+)$#', $route, $m) && ($_ANIME = DB::fetch("SELECT * FROM anime WHERE slug = ?", [$m[1]])):
        $_GET['slug'] = $m[1];
        $pageFile = __DIR__ . '/pages/watch.php';
        $pageTitle = 'Watch ' . ($_ANIME['title'] ?? '') . ' - ' . SITE_NAME;
        break;

    // Genre
    case preg_match('#^/genre/([a-z0-9\-]+)$#', $route, $m):
        $_GET['slug'] = $m[1];
        $pageFile = __DIR__ . '/pages/genre.php';
        $genre = DB::fetch("SELECT * FROM genres WHERE slug = ?", [$m[1]]);
        $pageTitle = ($genre['name'] ?? 'Genre') . ' Anime - ' . SITE_NAME;
        break;

    // AZ List / Filter
    case $route === '/filter' || $route === '/az-list':
        $pageFile = __DIR__ . '/pages/filter.php';
        $pageTitle = 'Browse Anime - ' . SITE_NAME;
        break;

    // Search AJAX
    case $route === '/search/ajax' && $method === 'GET':
        require __DIR__ . '/pages/search-ajax.php';
        exit;

    // Random
    case $route === '/random':
        $rand = DB::fetch("SELECT slug FROM anime ORDER BY RAND() LIMIT 1");
        if ($rand) redirect($rand['slug']);
        redirect('');
        break;

    // Static pages
    case in_array($route, ['/about', '/faq', '/contact', '/dmca', '/terms']):
        $pageFile = __DIR__ . '/pages/static.php';
        $_GET['page'] = trim($route, '/');
        $pageTitle = ucfirst(trim($route, '/')) . ' - ' . SITE_NAME;
        break;

    // 404
    default:
        http_response_code(404);
        $pageFile = __DIR__ . '/pages/static.php';
        $_GET['page'] = '404';
        $pageTitle = '404 Not Found - ' . SITE_NAME;
}

// ---- RENDER PAGE ----
ob_start();
if ($pageFile && file_exists($pageFile)) {
    require $pageFile;
} else {
    echo '<h1>Page not found</h1>';
}
$content = ob_get_clean();

require __DIR__ . '/includes/layout.php';
