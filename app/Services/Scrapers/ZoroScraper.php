<?php

namespace App\Services\Scrapers;

use Illuminate\Support\Facades\Http;

class ZoroScraper implements ScraperInterface
{
    protected string $baseUrl = 'https://aniwatch.to';

    public function name(): string
    {
        return 'Zoro (AniWatch)';
    }

    public function search(string $query): array
    {
        $url = "{$this->baseUrl}/search?keyword=" . urlencode($query);
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ])->get($url);

        if (!$response->successful()) return [];

        $html = $response->body();
        $results = [];

        preg_match_all('/<a href="\/anime\/([^"]+)"[^>]*>.*?<img[^>]+src="([^"]+)"[^>]*>.*?<h3[^>]*>(.*?)<\/h3>/s', $html, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $results[] = [
                'id' => $match[1],
                'title' => trim(strip_tags($match[3])),
                'image' => $match[2],
            ];
        }

        return $results;
    }

    public function getEpisodes(string $animeId): array
    {
        $url = "{$this->baseUrl}/anime/{$animeId}";
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ])->get($url);

        if (!$response->successful()) return [];

        $html = $response->body();
        $episodes = [];
        $idMap = [];

        preg_match_all('/<a[^>]+href="\/watch\/episode-([^"]+)"[^>]*>.*?Episode\s*(\d+)/s', $html, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $epNum = (int)$m[2];
            if (!isset($idMap[$epNum])) {
                $idMap[$epNum] = true;
                $episodes[] = [
                    'id' => $m[1],
                    'number' => $epNum,
                ];
            }
        }

        usort($episodes, fn($a, $b) => $a['number'] - $b['number']);
        return $episodes;
    }

    public function getVideoUrl(string $episodeId): ?string
    {
        $url = "{$this->baseUrl}/watch/episode-{$episodeId}";
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ])->get($url);

        if (!$response->successful()) return null;

        $html = $response->body();

        if (preg_match('/<iframe[^>]+src="([^"]+)"[^>]*>/i', $html, $match)) {
            return $match[1];
        }

        return null;
    }
}
