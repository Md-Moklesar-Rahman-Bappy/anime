<?php

namespace App\Services\Scrapers;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MangafireScraper
{
    protected string $baseUrl = 'https://mangafire.to';

    protected int $requestDelay = 1500000;

    protected array $headers = [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'en-US,en;q=0.5',
    ];

    public function setRequestDelay(int $microseconds): void
    {
        $this->requestDelay = $microseconds;
    }

    public function getTotalPages(): int
    {
        $html = $this->fetch('/filter');
        if (! $html) {
            return 0;
        }

        $dom = new DOMDocument;
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);

        $pageLinks = $xpath->query('//a[contains(@href, "filter?page=")]');
        $maxPage = 0;

        foreach ($pageLinks as $link) {
            if (preg_match('/page=(\d+)/', $link->getAttribute('href'), $m)) {
                $maxPage = max($maxPage, (int) $m[1]);
            }
        }

        return $maxPage ?: 1;
    }

    public function scrapePage(int $page = 1): array
    {
        $url = $page === 1 ? '/filter' : "/filter?page={$page}";
        $html = $this->fetch($url);
        if (! $html) {
            return [];
        }

        $dom = new DOMDocument;
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);

        $results = [];
        $cards = $xpath->query('//a[starts-with(@href, "/manga/")]');

        $seenSlugs = [];

        foreach ($cards as $card) {
            $href = $card->getAttribute('href');
            if (! preg_match('#^/manga/([^/]+)$#', $href, $m)) {
                continue;
            }

            $title = trim($card->textContent);
            if (empty($title)) {
                continue;
            }

            $slug = $m[1];
            if (isset($seenSlugs[$slug])) {
                continue;
            }
            $seenSlugs[$slug] = true;

            $parent = $card->parentNode;

            $type = null;
            $typeSpan = $xpath->query('.//span[contains(@class, "type")]', $parent)->item(0);
            if ($typeSpan) {
                $type = trim($typeSpan->textContent);
            }

            $img = $xpath->query('.//img', $parent)->item(0);
            $thumbnail = $img ? $img->getAttribute('src') : null;

            if (! $thumbnail) {
                $poster = $xpath->query('preceding::a[contains(@class, "poster")]//img', $card)->item(0);
                if ($poster) {
                    $thumbnail = $poster->getAttribute('src');
                }
            }

            $results[] = [
                'slug' => $slug,
                'title' => $title,
                'thumbnail' => $thumbnail,
                'type' => $type,
            ];
        }

        return $results;
    }

    public function scrapeMangaDetail(string $slug): ?array
    {
        $html = $this->fetch("/manga/{$slug}");
        if (! $html) {
            return null;
        }

        $dom = new DOMDocument;
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);

        $title = $this->getNodeText($xpath, '//h1');

        $altTitles = [];
        $h6 = $xpath->query('//h6')->item(0);
        if ($h6) {
            $altText = trim($h6->textContent);
            if ($altText) {
                $altTitles = array_map('trim', explode(';', $altText));
            }
        }

            $description = '';
            $descDiv = $xpath->query('//div[contains(@class, "description")]')->item(0);
            if ($descDiv) {
                $descText = trim($descDiv->textContent);
                if (str_ends_with($descText, '...')) {
                    $synopsisModal = $xpath->query('//div[contains(@class, "modal-content")]')->item(0);
                    if ($synopsisModal) {
                        $fullText = trim($synopsisModal->textContent);
                        $fullText = preg_replace('/\s+/', ' ', $fullText);
                        if (strlen($fullText) > strlen($descText)) {
                            $descText = $fullText;
                        }
                    }
                }
                $description = $descText;
            }
            if (empty($description)) {
                if (preg_match('/<div class="modal-content[^"]*">\s*([\s\S]*?)<\/div>\s*<\/div>\s*<\/div>\s*<script/', $html, $m)) {
                    $description = trim(strip_tags($m[1]));
                    $description = preg_replace('/\s+/', ' ', $description);
                }
            }

        $status = null;
        $statusNodes = $xpath->query('//*[contains(@class, "status") or contains(@class, "badge")]');
        foreach ($statusNodes as $node) {
            $text = trim($node->textContent);
            if (in_array($text, ['Releasing', 'Completed', 'On Hiatus', 'Discontinued', 'Not Yet Published'])) {
                $status = $text;
                break;
            }
        }
        if (! $status) {
            if (preg_match('/>\s*(Releasing|Completed|On Hiatus|Discontinued|Not Yet Published)\s*</', $html, $m)) {
                $status = $m[1];
            }
        }

        $type = null;
        $typeLinks = $xpath->query('//a[starts-with(@href, "/type/")]');
        if ($typeLinks->length > 0) {
            $type = ucfirst(trim($typeLinks->item(0)->textContent));
        }

        $score = null;
        $scorePattern = '/>\s*([\d.]+)\s*MAL\s*</';
        if (preg_match($scorePattern, $html, $m)) {
            $score = (float) $m[1];
        }

        $rating = null;
        $ratingPattern = '/>\s*([\d.]+)\s*\/\s*10\s*</';
        if (preg_match($ratingPattern, $html, $m)) {
            $rating = (float) $m[1];
        }

        $author = null;
        $authorLinks = $xpath->query('//a[starts-with(@href, "/author/")]');
        if ($authorLinks->length > 0) {
            $authors = [];
            foreach ($authorLinks as $link) {
                $authors[] = trim($link->textContent);
            }
            $author = implode(', ', $authors);
        }

        $artist = null;
        $artistLinks = $xpath->query('//a[starts-with(@href, "/artist/")]');
        if ($artistLinks->length > 0) {
            $artists = [];
            foreach ($artistLinks as $link) {
                $artists[] = trim($link->textContent);
            }
            $artist = implode(', ', $artists);
        }

        $year = null;
        $publishedPattern = '/(\d{4})\s*to/i';
        if (preg_match($publishedPattern, $html, $m)) {
            $year = (int) $m[1];
        }

        $genres = [];
        $genreLinks = $xpath->query('//a[starts-with(@href, "/genre/")]');
        foreach ($genreLinks as $link) {
            $genreName = trim($link->textContent);
            if ($genreName && $genreName !== 'All') {
                $genres[] = $genreName;
            }
        }
        $genres = array_unique($genres);
        $genres = array_values($genres);

        $thumbnail = null;
        $coverImg = $xpath->query('//img[contains(@class, "cover") or contains(@alt, "cover")]')->item(0)
            ?: $xpath->query('(//img[starts-with(@src, "https://static.mfcdn.nl")])[1]')->item(0);
        if ($coverImg) {
            $thumbnail = $coverImg->getAttribute('src');
        }

        $banner = null;
        $bannerImg = $xpath->query('//img[contains(@class, "banner")]')->item(0);
        if ($bannerImg) {
            $banner = $bannerImg->getAttribute('src');
        }

        $chaptersCount = null;
        if (preg_match('/English\s*\((\d+)\s*Chapters?\)/i', $html, $m)) {
            $chaptersCount = (int) $m[1];
        }

        $thumbKey = null;
        if ($thumbnail) {
            if (preg_match('/\/([^\/]+)\.(jpg|jpeg|png|webp)/', $thumbnail, $m)) {
                $thumbKey = $m[1];
            }
            if ($thumbKey) {
                $banner = "https://static.mfcdn.nl/eb19/i/0/0e/{$thumbKey}.jpg";
            }
        }

        return [
            'title' => $title ?: $slug,
            'slug' => $slug,
            'description' => $description ?: null,
            'alternative_titles' => ! empty($altTitles) ? implode('; ', $altTitles) : null,
            'type' => $type,
            'status' => $status ? ($status === 'Releasing' ? 'Ongoing' : $status) : null,
            'year' => $year,
            'rating' => $rating,
            'score' => $score,
            'chapters_count' => $chaptersCount,
            'author' => $author,
            'artist' => $artist,
            'thumbnail' => $thumbnail,
            'banner' => $banner,
            'genres' => $genres,
        ];
    }

    public function scrapeChapters(string $slug, string $language = 'en'): array
    {
        $html = $this->fetch("/manga/{$slug}");
        if (! $html) {
            return [];
        }

        $dom = new DOMDocument;
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);

        $chapters = [];

        $langNodes = $xpath->query("//a[contains(@href, '/{$slug}/{$language}/chapter-')]");
        if ($langNodes->length === 0) {
            $langNodes = $xpath->query("//a[contains(@href, '/{$slug}/en/chapter-')]");
        }
        if ($langNodes->length === 0) {
            $langNodes = $xpath->query("//a[contains(@href, '/{$slug}/') and contains(@href, '/chapter-')]");
        }

        $seen = [];
        foreach ($langNodes as $node) {
            $href = $node->getAttribute('href');
            if (! preg_match('#/chapter-([\d.]+)#', $href, $m)) {
                continue;
            }

            $num = $m[1];
            if (isset($seen[$num])) {
                continue;
            }
            $seen[$num] = true;

            $title = trim($node->textContent);
            $title = preg_replace('/^Chapter\s+[\d.]+\s*/i', '', $title);
            $title = $title ?: null;

            $dateStr = null;
            $parent = $node->parentNode;
            if ($parent) {
                $fullText = $parent->textContent;
                if (preg_match('/([A-Z][a-z]+ \d{1,2},? \d{4})/', $fullText, $dm)) {
                    $dateStr = $dm[1];
                }
            }

            $chapters[] = [
                'number' => (float) $num,
                'title' => $title,
                'date' => $dateStr,
            ];
        }

        usort($chapters, fn ($a, $b) => $a['number'] <=> $b['number']);

        return $chapters;
    }

    protected function fetch(string $path): ?string
    {
        static $lastRequest = 0;

        $now = (int) (microtime(true) * 1000000);
        $elapsed = $now - $lastRequest;
        if ($elapsed < $this->requestDelay) {
            usleep((int) ($this->requestDelay - $elapsed));
        }

        $url = $this->baseUrl . $path;

        try {
            $response = Http::withHeaders($this->headers)
                ->timeout(30)
                ->get($url);

            $lastRequest = (int) (microtime(true) * 1000000);

            if (! $response->successful()) {
                return null;
            }

            return $response->body();
        } catch (\Exception $e) {
            $lastRequest = (int) (microtime(true) * 1000000);
            return null;
        }
    }

    protected function getNodeText(DOMXPath $xpath, string $query): ?string
    {
        $node = $xpath->query($query)->item(0);
        return $node ? trim($node->textContent) : null;
    }
}
