<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$url = $_GET['url'] ?? '';
if (!$url) {
    http_response_code(400);
    echo json_encode(['error' => 'URL required']);
    exit;
}

// Allowed base domains
$allowed = ['ftp5.circleftp.net', '172.16.50.14'];
$parsed = parse_url($url);
if (!$parsed || !in_array($parsed['host'] ?? '', $allowed)) {
    http_response_code(403);
    echo json_encode(['error' => 'Domain not allowed']);
    exit;
}

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
$html = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if (!$html || $http_code !== 200) {
    http_response_code(502);
    echo json_encode(['error' => 'Failed to fetch URL (HTTP ' . $http_code . ')']);
    exit;
}

$dom = new DOMDocument();
@$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
$xpath = new DOMXPath($dom);

// Detect h5ai version from comment or unique class
$is_h5ai = strpos($html, 'h5ai') !== false;

$entries = [];
$rows = $xpath->query("//tr");
foreach ($rows as $row) {
    $tds = $xpath->query(".//td", $row);
    if ($tds->length < 2) continue;

    $icon_td = $tds->item(0);
    $name_td = $tds->item(1);
    $img = $xpath->query(".//img", $icon_td)->item(0);
    $link = $xpath->query(".//a", $name_td)->item(0);

    if (!$link || !$img) continue;
    $src = $img->getAttribute('src');
    $href = $link->getAttribute('href');
    $name = trim($link->textContent);

    if ($name === 'Parent Directory' || $name === '..') continue;
    if (!$href || $name === '') continue;

    $is_dir = strpos($src, 'folder') !== false;

    $entry = [
        'name' => $name,
        'url' => $href,
        'is_dir' => $is_dir,
        'date' => '',
        'size' => '',
    ];

    if ($tds->length >= 3) {
        $entry['date'] = trim($tds->item(2)->textContent);
    }
    if ($tds->length >= 4 && !$is_dir) {
        $size_raw = trim($tds->item(3)->textContent);
        $entry['size'] = $size_raw;
        // Parse KB/MB/GB
        if (preg_match('/^([\d.]+)\s*(KB|MB|GB)/i', $size_raw, $m)) {
            $val = (float)$m[1];
            $unit = strtoupper($m[2]);
            if ($unit === 'KB') $entry['size_bytes'] = round($val * 1024);
            elseif ($unit === 'MB') $entry['size_bytes'] = round($val * 1024 * 1024);
            elseif ($unit === 'GB') $entry['size_bytes'] = round($val * 1024 * 1024 * 1024);
        }
    }

    // Absolute URL
    if (!str_starts_with($href, 'http')) {
        $base = rtrim($url, '/') . '/';
        if (str_starts_with($href, '/')) {
            $base_parsed = parse_url($url);
            $base = $base_parsed['scheme'] . '://' . $base_parsed['host'] . $href;
        } else {
            $entry['url'] = $base . ltrim($href, '/');
        }
    }

    $entries[] = $entry;
}

echo json_encode(['entries' => $entries, 'url' => $url, 'is_h5ai' => $is_h5ai]);
