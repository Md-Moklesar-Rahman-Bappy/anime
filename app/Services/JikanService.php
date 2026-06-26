<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class JikanService
{
    protected string $baseUrl;

    protected int $timeout;

    protected int $retry;

    protected int $retryDelay;

    public ?string $lastError = null;

    public ?array $lastPagination = null;

    public function __construct()
    {
        $this->baseUrl = config('services.jikan.base_url', 'https://api.jikan.moe/v4');
        $this->timeout = config('services.jikan.timeout', 15);
        $this->retry = config('services.jikan.retry', 3);
        $this->retryDelay = config('services.jikan.retry_delay', 100);
    }

    protected function request(string $endpoint, array $params = []): array
    {
        $this->lastError = null;

        try {
            $response = Http::baseUrl($this->baseUrl)
                ->timeout($this->timeout)
                ->retry($this->retry, $this->retryDelay * 10, fn ($e) => $e->response && $e->response->status() >= 500)
                ->withUserAgent('Mozilla/5.0 (compatible; AnimeCatalog/1.0)')
                ->acceptJson()
                ->get($endpoint, $params);

            $this->rateLimit();

            if ($response->successful()) {
                return $response->json();
            }

            if ($response->status() === 429) {
                $retryAfter = (int) $response->header('Retry-After', 5);
                sleep(min($retryAfter, 10));
                $response = Http::baseUrl($this->baseUrl)
                    ->timeout($this->timeout)
                    ->withUserAgent('Mozilla/5.0 (compatible; AnimeCatalog/1.0)')
                    ->acceptJson()
                    ->get($endpoint, $params);
                if ($response->successful()) {
                    return $response->json();
                }
            }

            $body = $response->json();
            $this->lastError = $body['message'] ?? $body['error'] ?? "Jikan API returned HTTP {$response->status()}";

            return [];
        } catch (Exception $e) {
            $this->lastError = 'Failed to connect to Jikan API: '.$e->getMessage();

            return [];
        }
    }

    protected function rateLimit(): void
    {
        usleep(350000);
    }

    public function searchAnime(string $query, int $page = 1): Collection
    {
        $data = $this->request('/anime', ['q' => $query, 'page' => $page, 'sfw' => true]);

        $this->lastPagination = $data['pagination'] ?? null;

        return collect($data['data'] ?? [])->map(fn ($item) => $this->mapAnime($item));
    }

    public function getAnime(int $malId): ?array
    {
        $data = $this->request("/anime/{$malId}/full");

        return isset($data['data']) ? $this->mapAnime($data['data']) : null;
    }

    public function getAnimeEpisodes(int $malId, int $page = 1): Collection
    {
        $data = $this->request("/anime/{$malId}/episodes", ['page' => $page]);

        return collect($data['data'] ?? [])->map(fn ($item) => $this->mapEpisode($item));
    }

    public function getTopAnime(string $filter = 'all', int $page = 1, int $limit = 25): Collection
    {
        $params = ['page' => $page, 'limit' => min($limit, 25)];
        if ($filter !== 'all') {
            $params['filter'] = $filter;
        }

        $data = $this->request('/top/anime', $params);

        $results = collect($data['data'] ?? [])->map(fn ($item) => $this->mapAnime($item));

        if ($limit > 25 && isset($data['pagination']['has_next_page']) && $data['pagination']['has_next_page']) {
            $remaining = $limit - 25;
            if ($remaining > 0) {
                $results = $results->merge($this->getTopAnime($filter, $page + 1, $remaining));
            }
        }

        return $results;
    }

    public function getSeasonalAnime(int $year, string $season, int $page = 1): Collection
    {
        $data = $this->request("/seasons/{$year}/{$season}", ['page' => $page, 'sfw' => true]);

        return collect($data['data'] ?? [])->map(fn ($item) => $this->mapAnime($item));
    }

    public function getCurrentSeason(int $page = 1): Collection
    {
        $data = $this->request('/seasons/now', ['page' => $page, 'sfw' => true]);

        return collect($data['data'] ?? [])->map(fn ($item) => $this->mapAnime($item));
    }

    public function getPaginationInfo(string $endpoint, array $params = []): ?array
    {
        $data = $this->request($endpoint, $params);

        return $data['pagination'] ?? null;
    }

    public function searchPagination(string $query, int $page = 1): ?array
    {
        $data = $this->request('/anime', ['q' => $query, 'page' => $page, 'sfw' => true]);

        return $data['pagination'] ?? null;
    }

    public function browseAnime(int $page = 1, string $orderBy = 'mal_id', string $sort = 'asc'): Collection
    {
        $data = $this->request('/anime', [
            'page' => $page,
            'limit' => 25,
            'order_by' => $orderBy,
            'sort' => $sort,
            'sfw' => true,
        ]);

        $this->lastPagination = $data['pagination'] ?? null;

        return collect($data['data'] ?? [])->map(fn ($item) => $this->mapAnime($item));
    }

    public function browsePagination(): ?array
    {
        if ($this->lastPagination !== null) {
            $pagination = $this->lastPagination;
            $this->lastPagination = null;
            return $pagination;
        }

        return null;
    }

    public function getAllEpisodes(int $malId): Collection
    {
        $all = collect();
        $page = 1;

        while (true) {
            $data = $this->request("/anime/{$malId}/episodes", ['page' => $page]);
            $episodes = collect($data['data'] ?? [])->map(fn ($item) => $this->mapEpisode($item));
            if ($episodes->isEmpty()) {
                break;
            }
            $all = $all->merge($episodes);

            if (! ($data['pagination']['has_next_page'] ?? false)) {
                break;
            }
            $page++;
        }

        return $all;
    }

    protected function mapAnime(array $item): array
    {
        $images = $item['images']['jpg'] ?? $item['images']['webp'] ?? [];
        $trailerImages = $item['trailer']['images'] ?? [];

        $statusMap = [
            'Currently Airing' => 'Ongoing',
            'Finished Airing' => 'Completed',
            'Not yet aired' => 'Upcoming',
        ];

        $synopsis = $item['synopsis'] ?? null;
        if ($synopsis) {
            $synopsis = preg_replace('/\s*\[Written by MAL Rewrite\]\s*/', '', $synopsis);
        }

        return [
            'mal_id' => $item['mal_id'],
            'title' => $item['title_english'] ?: $item['title'],
            'title_japanese' => $item['title_japanese'] ?? null,
            'slug' => null,
            'description' => $synopsis,
            'type' => $item['type'] ?? null,
            'status' => $statusMap[$item['status'] ?? ''] ?? $item['status'],
            'country' => 'JP',
            'season' => $item['season'] ? ucfirst($item['season']) : null,
            'year' => $item['year'] ?? null,
            'rating' => $item['rating'] ?? null,
            'score' => $item['score'] ?? null,
            'episodes_count' => $item['episodes'] ?? 0,
            'duration' => $this->parseDuration($item['duration'] ?? null),
            'source' => $item['source'] ?? null,
            'studio' => collect($item['studios'] ?? [])->pluck('name')->implode(', '),
            'producers' => collect($item['producers'] ?? [])->pluck('name')->implode(', '),
            'licensors' => collect($item['licensors'] ?? [])->pluck('name')->implode(', '),
            'thumbnail' => $images['large_image_url'] ?? $images['image_url'] ?? null,
            'banner' => $trailerImages['maximum_image_url'] ?? $trailerImages['large_image_url'] ?? null,
            'genres' => collect($item['genres'] ?? [])->map(fn ($g) => [
                'mal_id' => $g['mal_id'],
                'name' => $g['name'],
            ])->values()->toArray(),
        ];
    }

    protected function mapEpisode(array $item): array
    {
        $images = $item['images']['jpg'] ?? [];

        return [
            'number' => $item['episode'] ?? $item['mal_id'],
            'title' => $item['title'] ?? null,
            'title_japanese' => $item['title_japanese'] ?? null,
            'air_date' => $item['aired'] ?? null,
            'duration' => $this->parseDuration($item['duration'] ?? null),
            'thumbnail' => $images['image_url'] ?? null,
            'synopsis' => $item['synopsis'] ?? null,
            'filler' => $item['filler'] ?? false,
            'recap' => $item['recap'] ?? false,
        ];
    }

    protected function parseDuration(?string $duration): ?int
    {
        if (! $duration) {
            return null;
        }

        preg_match('/(\d+)\s*min/', $duration, $matches);

        return isset($matches[1]) ? (int) $matches[1] : null;
    }
}
