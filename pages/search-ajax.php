<?php
try {
    $q = $_GET['q'] ?? '';
    if (strlen($q) < 2) { echo json_encode([]); exit; }

    $results = DB::fetchAll(
        "SELECT id, title, slug, type, year, thumbnail, rating
         FROM anime
         WHERE title LIKE ? OR title_japanese LIKE ?
         LIMIT 8",
        ["%$q%", "%$q%"]
    );

    $html = '';
    foreach ($results as $r) {
        $thumb = $r['thumbnail'] ?: 'https://via.placeholder.com/40x56/1a1a2e/cccccc?text=N';
        $html .= '<a href="' . url($r['slug']) . '" class="suggestion-item">';
        $html .= '<img src="' . escape($thumb) . '" alt="" class="suggestion-thumb">';
        $html .= '<div class="suggestion-info">';
        $html .= '<span class="suggestion-title">' . escape($r['title']) . '</span>';
        $html .= '<span class="suggestion-meta">' . ($r['type'] ?? '') . ' &middot; ' . ($r['year'] ?? '') . ' &middot; <i class="fas fa-star"></i> ' . ($r['rating'] ?? '?') . '</span>';
        $html .= '</div></a>';
    }

    header('Content-Type: application/json');
    echo json_encode(['html' => $html, 'count' => count($results)]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Search failed']);
}
