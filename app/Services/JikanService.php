<?php

namespace App\Services;

use App\Exceptions\JikanApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        $this->baseUrl = rtrim(config('services.jikan.base_url', 'https://api.jikan.moe/v4'), '/');
        $this->timeout = (int) config('services.jikan.timeout', 30);
        $this->connectTimeout = (int) config('services.jikan.connect_timeout', 15);
        $this->retry = (int) config('services.jikan.retry', 3);
        $this->retryDelay = (int) config('services.jikan.retry_delay', 500); // milliseconds
    }

    public function getPagination(): ?array
    {
        return $this->pagination;
    }

    public function searchAnime(string $query, int $page = 1): Collection
    {
        $data = $this->request('/anime', [
            'q' => $query,
            'page' => max(1, $page),
            'sfw' => true,
        ]);

        return collect($data['data'] ?? [])
            ->map(fn ($item) => $this->mapAnime($item))
            ->values();
    }

    public function getAnime(int $malId): array
    {
        $data = $this->request("/anime/{$malId}/full");

        if (!isset($data['data']) || !is_array($data['data'])) {
            throw JikanApiException::notFound();
        }

        return $this->mapAnime($data['data']);
    }

    public function getAnimeEpisodes(int $malId, int $page = 1): Collection
    {
        $data = $this->request("/anime/{$malId}/episodes", [
            'page' => max(1, $page),
        ]);

        return collect($data['data'] ?? [])
            ->map(fn ($item) => $this->mapEpisode($item))
            ->values();
    }

    public function getTopAnime(string $filter = 'all', int $page = 1, int $limit = 25): Collection
    {
        $remaining = max(1, $limit);
        $currentPage = max(1, $page);
        $results = collect();

        while ($remaining > 0) {
            $pageLimit = min($remaining, 25);

            $params = [
                'page' => $currentPage,
                'limit' => $pageLimit,
                'sfw' => true,
            ];

            if ($filter !== 'all') {
                $params['filter'] = $filter;
            }

            $data = $this->request('/top/anime', $params);

            $chunk = collect($data['data'] ?? [])
                ->map(fn ($item) => $this->mapAnime($item));

            $results = $results->merge($chunk);

            $remaining -= $chunk->count();

            if (!($data['pagination']['has_next_page'] ?? false)) {
                break;
            }

            $currentPage++;
        }

        return $results->take($limit)->values();
    }

    public function getSeasonalAnime(int $year, string $season, int $page = 1): Collection
    {
        $season = strtolower(trim($season));

        $data = $this->request("/seasons/{$year}/{$season}", [
            'page' => max(1, $page),
            'sfw' => true,
        ]);

        return collect($data['data'] ?? [])
            ->map(fn ($item) => $this->mapAnime($item))
            ->values();
    }

    public function getCurrentSeason(int $page = 1): Collection
    {
        $data = $this->request('/seasons/now', [
            'page' => max(1, $page),
            'sfw' => true,
        ]);

        return collect($data['data'] ?? [])
            ->map(fn ($item) => $this->mapAnime($item))
            ->values();
    }

    public function browseAnime(int $page = 1, string $orderBy = 'mal_id', string $sort = 'asc'): Collection
    {
        $data = $this->request('/anime', [
            'page' => max(1, $page),
            'limit' => 25,
            'order_by' => $orderBy,
            'sort' => $sort,
            'sfw' => true,
        ]);

        return collect($data['data'] ?? [])
            ->map(fn ($item) => $this->mapAnime($item))
            ->values();
    }

    public function browsePagination(int $page = 1, string $orderBy = 'mal_id', string $sort = 'asc'): array
    {
        $data = $this->request('/anime', [
            'page' => max(1, $page),
            'limit' => 1,
            'order_by' => $orderBy,
            'sort' => $sort,
            'sfw' => true,
        ]);

        return $data['pagination'] ?? [];
    }

    public function getGenres(): Collection
    {
        $data = $this->request('/genres/anime');

        return collect($data['data'] ?? [])
            ->map(function ($item) {
                return [
                    'mal_id' => $item['mal_id'] ?? null,
                    'name' => $item['name'] ?? null,
                ];
            })
            ->filter(fn ($g) => !empty($g['name']))
            ->values();
    }

    public function getAllEpisodes(int $malId): Collection
    {
        $all = collect();
        $page = 1;
        $maxPage = 1;

        do {
            try {
                $data = $this->request("/anime/{$malId}/episodes", [
                    'page' => $page,
                ]);
            } catch (JikanApiException $e) {
                // If later pages fail but we already have some episodes, return what we collected
                if ($page > 1 && $all->isNotEmpty()) {
                    Log::warning('Partial episode import from Jikan', [
                        'mal_id' => $malId,
                        'failed_page' => $page,
                        'error' => $e->getMessage(),
                    ]);

                    break;
                }

                throw $e;
            }

            $episodes = collect($data['data'] ?? [])
                ->map(fn ($item) => $this->mapEpisode($item))
                ->filter(fn ($ep) => !empty($ep['number']))
                ->values();

            $all = $all->merge($episodes);

            $pagination = $data['pagination'] ?? [];
            $maxPage = (int) ($pagination['last_visible_page'] ?? $page);

            $page++;
        } while ($page <= $maxPage);

        return $all
            ->unique(fn ($ep) => (string) $ep['number'])
            ->values();
    }

    protected function request(string $endpoint, array $params = []): array
    {
        $this->pagination = null;

        try {
            $response = Http::baseUrl($this->baseUrl)
                ->timeout($this->timeout)
                ->connectTimeout($this->connectTimeout)
                ->acceptJson()
                ->withUserAgent('AniWaves/1.0 (+Laravel)')
                ->retry(
                    $this->retry,
                    $this->retryDelay,
                    function ($exception, $request) {
                        if ($exception instanceof ConnectionException) {
                            return true;
                        }

                        if ($exception instanceof RequestException && $exception->response) {
                            return $exception->response->status() >= 500;
                        }

                        return false;
                    }
                )
                ->get($endpoint, $params);

            // Respect Jikan rate limits lightly
            $this->rateLimit();

            if ($response->successful()) {
                $data = $response->json();

                if (!is_array($data)) {
                    throw JikanApiException::badResponse(
                        $response->status(),
                        'Invalid JSON response'
                    );
                }

                $this->pagination = $data['pagination'] ?? null;

                return $data;
            }

            if ($response->status() === 404) {
                throw JikanApiException::notFound();
            }

            if ($response->status() === 429) {
                $retryAfter = (int) $response->header('Retry-After', 2);
                $retryAfter = max(1, min($retryAfter, 10));

                sleep($retryAfter);

                $retryResponse = Http::baseUrl($this->baseUrl)
                    ->timeout($this->timeout)
                    ->connectTimeout($this->connectTimeout)
                    ->acceptJson()
                    ->withUserAgent('AniWaves/1.0 (+Laravel)')
                    ->get($endpoint, $params);

                $this->rateLimit();

                if ($retryResponse->successful()) {
                    $data = $retryResponse->json();
                    $this->pagination = $data['pagination'] ?? null;

                    return is_array($data) ? $data : [];
                }

                throw JikanApiException::rateLimited($retryAfter);
            }

            $body = $response->json();
            $message = is_array($body)
                ? ($body['message'] ?? $body['error'] ?? "HTTP {$response->status()}")
                : "HTTP {$response->status()}";

            throw JikanApiException::badResponse($response->status(), (string) $message);

        } catch (ConnectionException $e) {
            Log::error('Jikan connection failed', [
                'endpoint' => $endpoint,
                'params' => $params,
                'error' => $e->getMessage(),
            ]);

            throw JikanApiException::connectionFailed($e->getMessage());
        } catch (JikanApiException $e) {
            Log::warning('Jikan API exception', [
                'endpoint' => $endpoint,
                'params' => $params,
                'message' => $e->getMessage(),
                'status' => $e->statusCode,
            ]);

            throw $e;
        } catch (\Throwable $e) {
            Log::error('Unexpected Jikan service error', [
                'endpoint' => $endpoint,
                'params' => $params,
                'error' => $e->getMessage(),
            ]);

            throw JikanApiException::connectionFailed($e->getMessage());
        }
    }

    protected function rateLimit(): void
    {
        // Jikan recommends spacing requests; keep it modest
        usleep(500000); // 0.5s
    }

    protected function mapAnime(array $item): array
    {
        $images = $item['images']['jpg'] ?? ($item['images']['webp'] ?? []);
        $trailerImages = $item['trailer']['images'] ?? [];

        $statusMap = [
            'Currently Airing' => 'Ongoing',
            'Finished Airing' => 'Completed',
            'Not yet aired' => 'Upcoming',
        ];

        $synopsis = $item['synopsis'] ?? null;
        if ($synopsis) {
            $synopsis = trim((string) preg_replace('/\s*\[Written by MAL Rewrite\]\s*/', '', $synopsis));
        }

        $titleEnglish = $item['title_english'] ?? null;
        $titleDefault = $item['title'] ?? null;

        return [
            'mal_id' => $item['mal_id'] ?? null,
            'title' => $titleEnglish ?: $titleDefault ?: 'Unknown',
            'title_japanese' => $item['title_japanese'] ?? null,
            'slug' => null,
            'description' => $synopsis,
            'type' => $item['type'] ?? null,
            'status' => $statusMap[$item['status'] ?? ''] ?? ($item['status'] ?? null),
            'country' => 'JP',
            'season' => !empty($item['season']) ? ucfirst((string) $item['season']) : null,
            'year' => isset($item['year']) && is_numeric($item['year']) ? (int) $item['year'] : null,
            'rating' => $item['rating'] ?? null,
            'score' => isset($item['score']) && is_numeric($item['score']) ? (float) $item['score'] : null,
            'episodes_count' => isset($item['episodes']) && is_numeric($item['episodes']) ? (int) $item['episodes'] : 0,
            'duration' => $this->parseDuration($item['duration'] ?? null),
            'source' => $item['source'] ?? null,
            'studio' => collect($item['studios'] ?? [])->pluck('name')->filter()->implode(', '),
            'producers' => collect($item['producers'] ?? [])->pluck('name')->filter()->implode(', '),
            'licensors' => collect($item['licensors'] ?? [])->pluck('name')->filter()->implode(', '),
            'thumbnail' => $images['large_image_url'] ?? $images['image_url'] ?? null,
            'banner' => $trailerImages['maximum_image_url'] ?? $trailerImages['large_image_url'] ?? null,
            'genres' => collect($item['genres'] ?? [])
                ->map(fn ($g) => [
                    'mal_id' => $g['mal_id'] ?? null,
                    'name' => $g['name'] ?? null,
                ])
                ->filter(fn ($g) => !empty($g['name']))
                ->values()
                ->toArray(),
        ];
    }

    protected function mapEpisode(array $item): array
    {
        $images = $item['images']['jpg'] ?? [];

        $number = $item['mal_id'] ?? null;
        if (isset($item['episode']) && is_numeric($item['episode'])) {
            $number = (int) $item['episode'];
        } elseif (isset($item['mal_id']) && is_numeric($item['mal_id'])) {
            $number = (int) $item['mal_id'];
        }

        return [
            'number' => $number,
            'title' => $item['title'] ?? null,
            'title_japanese' => $item['title_japanese'] ?? null,
            'air_date' => $this->parseAirDate($item['aired'] ?? null),
            'duration' => $this->parseDuration($item['duration'] ?? null),
            'thumbnail' => $images['image_url'] ?? null,
            'synopsis' => $item['synopsis'] ?? null,
            'filler' => (bool) ($item['filler'] ?? false),
            'recap' => (bool) ($item['recap'] ?? false),
        ];
    }

    protected function parseAirDate(?string $aired): ?string
    {
        if (!$aired) {
            return null;
        }

        try {
            $date = preg_replace('/T\d{2}:\d{2}:\d{2}\+00:00$/', '', $aired);

            if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return null;
            }

            return $date;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function parseDuration(?string $duration): ?int
    {
        if (!$duration) {
            return null;
        }

        if (preg_match('/(\d+)\s*min/i', $duration, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}