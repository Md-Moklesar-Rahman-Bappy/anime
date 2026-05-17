<?php

namespace App\Services\Scrapers;

use Illuminate\Support\Facades\Http;

class ZoroTvScraper implements ScraperInterface
{
    protected string $baseUrl = 'https://zorotv.com.ro';

    public function name(): string
    {
        return 'Zoro TV (zorotv.com.ro)';
    }

    public function search(string $query): array
    {
        $url = "{$this->baseUrl}/?s=".urlencode($query);
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ])->get($url);

        if (! $response->successful()) {
            return [];
        }

        $html = $response->body();
        $results = [];

        preg_match_all('/<a\s+href="https:\/\/zorotv\.com\.ro\/([^"]+)"[^>]*>([^<]+)<\/a>/s', $html, $matches, PREG_SET_ORDER);

        $seen = [];
        foreach ($matches as $match) {
            $postSlug = $match[1];
            $title = trim(strip_tags($match[2]));
            $animeName = preg_replace('/\s*Episode\s*\d+.*$/i', '', $title);
            $animeKey = strtolower(trim($animeName));

            if (isset($seen[$animeKey])) {
                continue;
            }
            $seen[$animeKey] = true;

            $results[] = [
                'id' => $postSlug,
                'title' => $title,
                'image' => null,
            ];
        }

        return $results;
    }

    public function getEpisodes(string $animeId): array
    {
        $url = "{$this->baseUrl}/feed/";
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ])->get($url);

        if (! $response->successful()) {
            return [];
        }

        $html = $response->body();
        $episodes = [];

        preg_match_all('/<item>.*?<title>(.*?)<\/title>.*?<link>(.*?)<\/link>.*?<category><!\[CDATA\[(.*?)\]\]><\/category>.*?<\/item>/s', $html, $matches, PREG_SET_ORDER);

        $animeSlug = strtolower(str_replace(' ', '-', trim($animeId)));

        foreach ($matches as $m) {
            $title = html_entity_decode(trim($m[1]));
            $link = trim($m[2]);
            $category = trim($m[3]);

            $catSlug = strtolower(str_replace(' ', '-', $category));

            if (preg_match('/\b'.preg_quote($animeSlug, '/').'\b/i', $catSlug)) {
                if (preg_match('/Episode\s*(\d+)/i', $title, $numMatch)) {
                    $epNum = (int) $numMatch[1];
                    $epSlug = basename(parse_url($link, PHP_URL_PATH));
                    $episodes[] = [
                        'id' => $epSlug,
                        'number' => $epNum,
                        'title' => $title,
                    ];
                }
            }
        }

        usort($episodes, fn ($a, $b) => $a['number'] - $b['number']);

        return $episodes;
    }

    public function getVideoUrl(string $episodeId): ?string
    {
        $url = "{$this->baseUrl}/{$episodeId}";
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ])->get($url);

        if (! $response->successful()) {
            return null;
        }

        $html = $response->body();

        if (preg_match('/<div\s+id="embed_holder"[^>]*>.*?<iframe[^>]+src="([^"]+)"[^>]*>/s', $html, $match)) {
            return $match[1];
        }

        if (preg_match('/<iframe[^>]+src="([^"]+)"[^>]*>/i', $html, $match)) {
            return $match[1];
        }

        return null;
    }
}
