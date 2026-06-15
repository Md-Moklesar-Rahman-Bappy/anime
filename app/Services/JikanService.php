<?php

namespace App\Services;

use App\Exceptions\JikanApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class JikanService
{
    protected string $baseUrl;

    protected int $timeout;

    protected int $connectTimeout;

    protected int $retry;

    protected int $retryDelay;

    protected ?array $pagination = null;

    public function __construct()
    {
        $this->baseUrl = config('services.jikan.base_url', 'https://api.jikan.moe/v4');
        $this->timeout = config('services.jikan.timeout', 30);
        $this->connectTimeout = config('services.jikan.connect_timeout', 15);
        $this->retry = config('services.jikan.retry', 5);
        $this->retryDelay = config('services.jikan.retry_delay', 200);
    }

    public function getPagination(): ?array
    {
        return $this->pagination;
    }

    public function searchAnime(string $query, int $page = 1): Collection
    {
        $data = $this->request('/anime', ['q' => $query, 'page' => $page, 'sfw' => true]);

        return collect($data['data'] ?? [])->map(fn ($item) => $this->mapAnime($item));
    }

    public function getAnime(int $malId): array
    {
        $data = $this->request("/anime/{$malId}/full");

        if (! isset($data['data'])) {
            throw JikanApiException::notFound();
        }

        return $this->mapAnime($data['data']);
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

        if ($limit > 25 && ($data['pagination']['has_next_page'] ?? false)) {
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

    public function browseAnime(int $page = 1, string $orderBy = 'mal_id', string $sort = 'asc'): Collection
    {
        $data = $this->request('/anime', [
            'page' => $page,
            'limit' => 25,
            'order_by' => $orderBy,
            'sort' => $sort,
            'sfw' => true,
        ]);

        return collect($data['data'] ?? [])->map(fn ($item) => $this->mapAnime($item));
    }

    public function getGenres(): Collection
    {
        $data = $this->request('/genres/anime');

        return collect($data['data'] ?? [])->map(fn ($item) => [
            'mal_id' => $item['mal_id'],
            'name' => $item['name'],
        ]);
    }

    public function getAllEpisodes(int $malId): Collection
    {
        $all = collect();
        $page = 1;

        while (true) {
            try {
                $data = $this->request("/anime/{$malId}/episodes", ['page' => $page, 'limit' => 100]);
            } catch (JikanApiException $e) {
                if ($page > 1 && $all->isNotEmpty()) {
                    break;
                }
                throw $e;
            }

            $episodes = collect($data['data'] ?? [])->map(fn ($item) => $this->mapEpisode($item));

            if ($page === 1) {
                $maxPage = $data['pagination']['last_visible_page'] ?? 1;

                if (! ($data['pagination']['has_next_page'] ?? false)) {
                    return $all->merge($episodes);
                }
            }

            if ($episodes->isNotEmpty()) {
                $all = $all->merge($episodes);
            }

            $page++;

            if ($page > $maxPage) {
                break;
            }
        }

        return $all;
    }

    protected function request(string $endpoint, array $params = []): array
    {
        $response = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->connectTimeout($this->connectTimeout)
            ->retry($this->retry, $this->retryDelay * 10, fn ($e) => $e instanceof ConnectionException || (isset($e->response) && $e->response->status() >= 500))
            ->withUserAgent('Mozilla/5.0 (compatible; AnimeCatalog/1.0)')
            ->acceptJson()
            ->get($endpoint, $params);

        $this->rateLimit();
        $this->pagination = null;

        if ($response->successful()) {
            $data = $response->json();
            $this->pagination = $data['pagination'] ?? null;

            return $data;
        }

        if ($response->status() === 429) {
            $retryAfter = min((int) $response->header('Retry-After', 5), 10);

            sleep($retryAfter);

            $retryResponse = Http::baseUrl($this->baseUrl)
                ->timeout($this->timeout)
                ->connectTimeout($this->connectTimeout)
                ->retry($this->retry, $this->retryDelay * 10, fn ($e) => $e instanceof ConnectionException || (isset($e->response) && $e->response->status() >= 500))
                ->withUserAgent('Mozilla/5.0 (compatible; AnimeCatalog/1.0)')
                ->acceptJson()
                ->get($endpoint, $params);

            if ($retryResponse->successful()) {
                $data = $retryResponse->json();
                $this->pagination = $data['pagination'] ?? null;

                return $data;
            }

            throw JikanApiException::rateLimited($retryAfter);
        }

        $body = $response->json();
        $message = $body['message'] ?? $body['error'] ?? "HTTP {$response->status()}";
        throw JikanApiException::badResponse($response->status(), $message);
    }

    protected function rateLimit(): void
    {
        usleep(1200000);
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
            'air_date' => $this->parseAirDate($item['aired'] ?? null),
            'duration' => $this->parseDuration($item['duration'] ?? null),
            'thumbnail' => $images['image_url'] ?? null,
            'synopsis' => $item['synopsis'] ?? null,
            'filler' => $item['filler'] ?? false,
            'recap' => $item['recap'] ?? false,
        ];
    }

    protected function parseAirDate(?string $aired): ?string
    {
        if (! $aired) {
            return null;
        }

        $date = preg_replace('/T\d{2}:\d{2}:\d{2}\+00:00$/', '', $aired);

        if (! $date || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }

        return $date;
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
