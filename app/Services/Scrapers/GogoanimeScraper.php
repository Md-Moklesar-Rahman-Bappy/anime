<?php

namespace App\Services\Scrapers;

use Illuminate\Support\Facades\Http;

class GogoanimeScraper implements ScraperInterface
{
    protected string $baseUrl = 'https://gogoanime3.co';

    public function name(): string
    {
        return 'Gogoanime';
    }

    public function search(string $query): array
    {
        $url = "{$this->baseUrl}/search.html?keyword=".urlencode($query);
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ])->get($url);

        if (! $response->successful()) {
            return [];
        }

        $html = $response->body();
        $results = [];

        preg_match_all('/<a href="\/category\/([^"]+)"[^>]*>.*?<img src="([^"]+)"[^>]*>.*?<p class="name">(.*?)<\/p>.*?<p class="released">(.*?)<\/p>/s', $html, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $results[] = [
                'id' => $match[1],
                'title' => trim(strip_tags($match[3])),
                'image' => $match[2],
                'released' => trim(strip_tags($match[4])),
            ];
        }

        return $results;
    }

    public function getEpisodes(string $animeId): array
    {
        $epStart = 0;
        $allEpisodes = [];

        do {
            $url = "{$this->baseUrl}/{$animeId}?ep={$epStart}";
            if ($epStart === 0) {
                $url = "{$this->baseUrl}/category/{$animeId}";
            }

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ])->get($url);

            if (! $response->successful()) {
                break;
            }

            $html = $response->body();

            preg_match_all('/<a href="\/([^"]+)"\s*class="[^"]*"?\s*ep="(\d+)"[^>]*>.*?<\/a>/s', $html, $matches, PREG_SET_ORDER);

            if (empty($matches)) {
                preg_match_all('/<li class="dowloads">.*?<a href="\/([^"]+)"[^>]*>.*?Episode\s*(\d+)/s', $html, $epMatches, PREG_SET_ORDER);
                foreach ($epMatches as $m) {
                    $allEpisodes[] = [
                        'id' => $m[1],
                        'number' => (int) $m[2],
                    ];
                }
                break;
            }

            foreach ($matches as $m) {
                $allEpisodes[] = [
                    'id' => $m[1],
                    'number' => (int) $m[2],
                ];
            }

            $epStart += 50;
        } while (count($matches) > 0);

        usort($allEpisodes, fn ($a, $b) => $a['number'] - $b['number']);

        return $allEpisodes;
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

        if (preg_match('/<iframe[^>]+src="([^"]+)"[^>]*>/i', $html, $match)) {
            return $match[1];
        }

        return null;
    }
}
