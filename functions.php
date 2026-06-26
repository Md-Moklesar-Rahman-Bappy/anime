<?php
require_once __DIR__ . '/db.php';

function asset($path): string {
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

function url($path = ''): string {
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect($path): void {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    // Avoid doubling BASE_URL if it's already part of the path
    if (str_starts_with($path, BASE_URL)) {
        header('Location: ' . $path);
    } else {
        header('Location: ' . url($path));
    }
    exit;
}

function escape($str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function truncate($str, $len = 120): string {
    if (mb_strlen($str) <= $len) return $str;
    return mb_substr($str, 0, $len) . '...';
}

function slugify($str): string {
    $str = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $str);
    $str = preg_replace('/[\s-]+/', '-', $str);
    return trim(mb_strtolower($str), '-');
}

function time_ago($timestamp): string {
    if (!$timestamp) return '';
    $diff = time() - strtotime($timestamp);
    $units = [
        31536000 => 'year', 2592000 => 'month', 604800 => 'week',
        86400 => 'day', 3600 => 'hour', 60 => 'minute', 1 => 'second'
    ];
    foreach ($units as $sec => $unit) {
        if ($diff >= $sec) {
            $count = floor($diff / $sec);
            return $count . ' ' . $unit . ($count > 1 ? 's' : '') . ' ago';
        }
    }
    return 'just now';
}

function is_auth(): bool {
    return isset($_SESSION['user_id']);
}

function is_admin(): bool {
    if (!is_auth()) return false;
    $user = current_user();
    if (!$user) return false;
    if (!empty($user['role_id'])) {
        $role = DB::fetch("SELECT level FROM roles WHERE id = ?", [$user['role_id']]);
        return $role && (int)$role['level'] >= 2;
    }
    return ($user['role'] ?? 'user') === 'admin';
}

function current_user(): ?array {
    if (!is_auth()) return null;
    return DB::fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

function require_auth(): void {
    if (!is_auth()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

function get_genres(): array {
    static $genres = null;
    if ($genres === null) {
        $genres = DB::fetchAll("SELECT * FROM genres ORDER BY name ASC");
    }
    return $genres;
}

function get_type_badge($type): string {
    $colors = [
        'TV' => '#3b82f6', 'Movie' => '#ef4444', 'OVA' => '#f59e0b',
        'ONA' => '#8b5cf6', 'Special' => '#10b981', 'Music' => '#ec4899'
    ];
    $color = $colors[$type] ?? '#6b7280';
    return '<span class="badge type-badge" style="background:' . $color . '">' . escape($type) . '</span>';
}

function get_status_badge($status): string {
    $colors = [
        'Currently Airing' => '#10b981',
        'Finished Airing'  => '#6b7280',
        'Not yet aired'    => '#f59e0b'
    ];
    $color = $colors[$status] ?? '#6b7280';
    return '<span class="badge status-badge" style="background:' . $color . '">' . escape($status === 'Currently Airing' ? 'Airing' : ($status === 'Finished Airing' ? 'Finished' : 'Upcoming')) . '</span>';
}

function anime_card(array $anime): string {
    $thumb = $anime['thumbnail'] ?: 'https://via.placeholder.com/225x320/1a1a2e/cccccc?text=No+Image';
    $title = escape($anime['title']);
    $slug  = escape($anime['slug']);
    $type  = get_type_badge($anime['type'] ?? '');
    $episodes = $anime['episodes_count'] ?? 0;
    $rating   = $anime['rating'] ?? '?';
    $views    = $anime['views'] ?? 0;

    return <<<HTML
    <a href="{$slug}" class="anime-card">
        <div class="anime-card-image">
            <img src="{$thumb}" alt="{$title}" loading="lazy">
            <div class="anime-card-overlay">
                <span class="play-icon"><i class="fas fa-play"></i></span>
            </div>
            <div class="anime-card-badges">
                {$type}
                <span class="badge ep-badge">{$episodes} ep</span>
            </div>
        </div>
        <div class="anime-card-body">
            <h3 class="anime-card-title">{$title}</h3>
            <div class="anime-card-meta">
                <span><i class="fas fa-star"></i> {$rating}</span>
                <span><i class="fas fa-eye"></i> {$views}</span>
            </div>
        </div>
    </a>
HTML;
}

function episode_card(array $episode): string {
    $anime   = DB::fetch("SELECT title, slug, thumbnail FROM anime WHERE id = ?", [$episode['anime_id']]);
    if (!$anime) return '';

    $thumb   = $episode['thumbnail'] ?: ($anime['thumbnail'] ?: 'https://via.placeholder.com/225x320/1a1a2e/cccccc?text=No+Image');
    $title   = escape($episode['title'] ?: 'Episode ' . $episode['number']);
    $animeTitle = escape($anime['title']);
    $slug    = escape($anime['slug']);
    $epNum   = (int)$episode['number'];
    $duration = $episode['duration'] ? gmdate('i:s', (int)$episode['duration']) : '';
    $type    = get_type_badge(DB::fetch("SELECT type FROM anime WHERE id = ?", [$episode['anime_id']])['type'] ?? '');
    $hasSub  = $episode['has_sub'] ?? 1;
    $hasDub  = $episode['has_dub'] ?? 0;

    $langBadges = '';
    if ($hasSub) $langBadges .= '<span class="badge lang-badge sub-badge">SUB</span>';
    if ($hasDub) $langBadges .= '<span class="badge lang-badge dub-badge">DUB</span>';

    return <<<HTML
    <a href="watch/{$slug}?ep={$epNum}" class="episode-card">
        <div class="episode-card-image">
            <img src="{$thumb}" alt="{$title}" loading="lazy">
            <div class="episode-card-overlay">
                <span class="play-icon"><i class="fas fa-play"></i></span>
            </div>
            <div class="episode-card-badges">
                {$type}
                <span class="badge ep-badge">Ep {$epNum}</span>
            </div>
        </div>
        <div class="episode-card-body">
            <h3 class="episode-card-title">{$animeTitle}</h3>
            <p class="episode-card-sub">{$title}</p>
            <div class="episode-card-meta">
                {$langBadges}
                <span><i class="far fa-clock"></i> {$duration}</span>
            </div>
        </div>
    </a>
HTML;
}

// ------------------ AUTH HELPERS ------------------

function auth_login(string $email, string $password): ?array {
    $user = DB::fetch("SELECT * FROM users WHERE email = ?", [$email]);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        return $user;
    }
    return null;
}

function auth_register(string $username, string $email, string $password): ?array {
    $exists = DB::fetch("SELECT id FROM users WHERE email = ? OR username = ?", [$email, $username]);
    if ($exists) return null;
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $id = DB::insert(
        "INSERT INTO users (username, email, password) VALUES (?, ?, ?)",
        [$username, $email, $hash]
    );
    $_SESSION['user_id'] = $id;
    return DB::fetch("SELECT * FROM users WHERE id = ?", [$id]);
}

function auth_logout(): void {
    unset($_SESSION['user_id']);
    session_destroy();
}

function log_activity(string $action, string $entity_type = null, $entity_id = null, array $details = []): void {
    $user_id = 0;
    $username = 'system';
    if (isset($GLOBALS['_user'])) {
        $user_id = (int)$GLOBALS['_user']['id'];
        $username = $GLOBALS['_user']['username'];
    } elseif (isset($_SESSION['user_id'])) {
        $user_id = (int)$_SESSION['user_id'];
        $u = DB::fetch("SELECT username FROM users WHERE id = ?", [$user_id]);
        if ($u) $username = $u['username'];
    }
    DB::insert(
        "INSERT INTO activity_logs (user_id, username, action, entity_type, entity_id, details, ip_address)
         VALUES (?, ?, ?, ?, ?, ?, ?)",
        [$user_id, $username, $action, $entity_type, $entity_id, json_encode($details), $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']
    );
}
