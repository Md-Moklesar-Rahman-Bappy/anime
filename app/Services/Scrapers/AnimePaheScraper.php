<?php

namespace App\Services\Scrapers;

use Illuminate\Support\Facades\Http;

class AnimePaheScraper implements ScraperInterface
{
    protected string $baseUrl = 'https://animepahe.ru';

    public function name(): string
    {
        return 'AnimePahe';
    }

    public function search(string $query): array
    {
        $url = "{$this->baseUrl}/api?m=search&q=".urlencode($query);
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ])->get($url);

        if (! $response->successful()) {
            return [];
        }

        $data = $response->json();
        $results = [];

        foreach ($data['data'] ?? [] as $item) {
            $results[] = [
                'id' => $item['id'],
                'title' => $item['title'],
                'image' => $item['poster'] ?? null,
                'type' => $item['type'] ?? null,
                'episodes' => $item['episodes'] ?? null,
            ];
        }

        return $results;
    }

    public function getEpisodes(string $animeId): array
    {
        $url = "{$this->baseUrl}/api?m=release&id={$animeId}&sort=episode_asc";
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ])->get($url);

        if (! $response->successful()) {
            return [];
        }

        $data = $response->json();
        $episodes = [];

        foreach ($data['data'] ?? [] as $item) {
            $episodes[] = [
                'id' => $item['session'] ?? $item['id'],
                'number' => (int) $item['episode'],
                'title' => $item['title'] ?? null,
            ];
        }

        return $episodes;
    }

    public function getVideoUrl(string $episodeId): ?string
    {
        $url = "{$this->baseUrl}/api?m=links&id={$episodeId}";
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ])->get($url);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        return $data['data'][0]['link'] ?? $data['data'][0]['url'] ?? null;
    }
}
