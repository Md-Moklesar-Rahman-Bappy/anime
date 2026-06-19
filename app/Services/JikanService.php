<?php

namespace App\Services;

use App\Exceptions\JikanApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class JikanService
{
    protected string $baseUrl;
    protected int $timeout;
    protected int $connectTimeout;
    protected int $retry;
    protected int $retryDelay;
    protected int $rateDelay;
    protected int $maxEpisodePages;

    protected ?array $pagination = null;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            config('services.jikan.base_url', 'https://api.jikan.moe/v4'),
            '/'
        );

        $this->timeout = (int) config('services.jikan.timeout', 30);
        $this->connectTimeout = (int) config('services.jikan.connect_timeout', 15);
        $this->retry = (int) config('services.jikan.retry', 3);
        $this->retryDelay = (int) config('services.jikan.retry_delay', 500);
        $this->rateDelay = (int) config('services.jikan.rate_delay', 500000);
        $this->maxEpisodePages = (int) config('services.jikan.max_episode_pages', 50);
    }

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    public function getPagination(): ?array
    {
        return $this->pagination;
    }

    /*
    |--------------------------------------------------------------------------
    | Search Anime
    |--------------------------------------------------------------------------
    */

    public function searchAnime(string $query, int $page = 1): Collection
    {
        $data = $this->request('/anime', [
            'q' => trim($query),
            'page' => max(1, $page),
            'sfw' => true,
        ]);

        return collect($data['data'] ?? [])
            ->map(fn($item) => $this->mapAnime($item))
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | Get Single Anime
    |--------------------------------------------------------------------------
    */

    public function getAnime(int $malId): array
    {
        $data = $this->request("/anime/{$malId}/full");

        if (!isset($data['data']) || !is_array($data['data'])) {
            throw JikanApiException::notFound();
        }

        return $this->mapAnime($data['data']);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Anime Episodes Page
    |--------------------------------------------------------------------------
    */

    public function getAnimeEpisodes(int $malId, int $page = 1): Collection
    {
        $data = $this->request("/anime/{$malId}/episodes", [
            'page' => max(1, $page),
        ]);

        return collect($data['data'] ?? [])
            ->map(fn($item) => $this->mapEpisode($item))
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | Top Anime
    |--------------------------------------------------------------------------
    */

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
                ->map(fn($item) => $this->mapAnime($item));

            $results = $results->merge($chunk);
            $remaining -= $chunk->count();

            if (!($data['pagination']['has_next_page'] ?? false)) {
                break;
            }

            $currentPage++;
        }

        return $results->take($limit)->values();
    }

    /*
    |--------------------------------------------------------------------------
    | Seasonal Anime
    |--------------------------------------------------------------------------
    */

    public function getSeasonalAnime(int $year, string $season, int $page = 1): Collection
    {
        $season = strtolower(trim($season));

        $data = $this->request("/seasons/{$year}/{$season}", [
            'page' => max(1, $page),
            'sfw' => true,
        ]);

        return collect($data['data'] ?? [])
            ->map(fn($item) => $this->mapAnime($item))
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | Current Season
    |--------------------------------------------------------------------------
    */

    public function getCurrentSeason(int $page = 1): Collection
    {
        $data = $this->request('/seasons/now', [
            'page' => max(1, $page),
            'sfw' => true,
        ]);

        return collect($data['data'] ?? [])
            ->map(fn($item) => $this->mapAnime($item))
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | Browse Anime
    |--------------------------------------------------------------------------
    */

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
            ->map(fn($item) => $this->mapAnime($item))
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | Browse Pagination
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Genres
    |--------------------------------------------------------------------------
    */

    public function getGenres(): Collection
    {
        $data = $this->request('/genres/anime');

        return collect($data['data'] ?? [])
            ->map(fn($item) => [
                'mal_id' => $item['mal_id'] ?? null,
                'name' => $this->cleanNullable($item['name'] ?? null),
            ])
            ->filter(fn($genre) => !empty($genre['name']))
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | All Episodes
    |--------------------------------------------------------------------------
    */

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
                if ($page > 1 && $all->isNotEmpty()) {
                    logger()->warning('Partial episode import from Jikan', [
                        'mal_id' => $malId,
                        'failed_page' => $page,
                        'error' => $e->getMessage(),
                    ]);

                    break;
                }

                throw $e;
            }

            $episodes = collect($data['data'] ?? [])
                ->map(fn($item) => $this->mapEpisode($item))
                ->filter(fn($episode) => !empty($episode['number']))
                ->values();

            $all = $all->merge($episodes);

            $pagination = $data['pagination'] ?? [];
            $maxPage = (int) ($pagination['last_visible_page'] ?? $page);

            $page++;

            if ($page > $this->maxEpisodePages) {
                logger()->warning('Jikan episode import stopped by max page limit', [
                    'mal_id' => $malId,
                    'max_episode_pages' => $this->maxEpisodePages,
                ]);

                break;
            }
        } while ($page <= $maxPage);

        return $all
            ->unique(fn($episode) => (string) $episode['number'])
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | HTTP Request
    |--------------------------------------------------------------------------
    */

    protected function request(string $endpoint, array $params = []): array
    {
        $this->pagination = null;

        try {
            $response = $this->client()
                ->retry(
                    $this->retry,
                    $this->retryDelay,
                    function ($exception) {
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

            $this->rateLimit();

            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */
            if ($response->successful()) {
                return $this->decodeResponse($response->json(), $response->status());
            }

            /*
            |--------------------------------------------------------------------------
            | Not Found
            |--------------------------------------------------------------------------
            */
            if ($response->status() === 404) {
                throw JikanApiException::notFound();
            }

            /*
            |--------------------------------------------------------------------------
            | Rate Limited
            |--------------------------------------------------------------------------
            */
            if ($response->status() === 429) {
                return $this->handleRateLimitedRequest($endpoint, $params, $response->header('Retry-After'));
            }

            /*
            |--------------------------------------------------------------------------
            | Bad Response
            |--------------------------------------------------------------------------
            */
            $body = $response->json();

            $message = is_array($body)
                ? ($body['message'] ?? $body['error'] ?? "HTTP {$response->status()}")
                : "HTTP {$response->status()}";

            throw JikanApiException::badResponse(
                $response->status(),
                (string) $message
            );
        } catch (ConnectionException $e) {
            logger()->error('Jikan connection failed', [
                'endpoint' => $endpoint,
                'params' => $params,
                'error' => $e->getMessage(),
            ]);

            throw JikanApiException::connectionFailed($e->getMessage());
        } catch (JikanApiException $e) {
            logger()->warning('Jikan API exception', [
                'endpoint' => $endpoint,
                'params' => $params,
                'message' => $e->getMessage(),
                'status' => $e->statusCode,
            ]);

            throw $e;
        } catch (\Throwable $e) {
            logger()->error('Unexpected Jikan service error', [
                'endpoint' => $endpoint,
                'params' => $params,
                'error' => $e->getMessage(),
            ]);

            throw JikanApiException::connectionFailed($e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HTTP Client
    |--------------------------------------------------------------------------
    */

    protected function client()
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->connectTimeout($this->connectTimeout)
            ->acceptJson()
            ->withUserAgent('AniWaves/1.0 (+Laravel)');
    }

    /*
    |--------------------------------------------------------------------------
    | Decode Response
    |--------------------------------------------------------------------------
    */

    protected function decodeResponse(mixed $data, int $status): array
    {
        if (!is_array($data)) {
            throw JikanApiException::badResponse(
                $status,
                'Invalid JSON response'
            );
        }

        $this->pagination = $data['pagination'] ?? null;

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | Handle 429
    |--------------------------------------------------------------------------
    */

    protected function handleRateLimitedRequest(string $endpoint, array $params, mixed $retryAfter): array
    {
        $retryAfter = (int) ($retryAfter ?: 2);
        $retryAfter = max(1, min($retryAfter, 10));

        sleep($retryAfter);

        $retryResponse = $this->client()->get($endpoint, $params);

        $this->rateLimit();

        if ($retryResponse->successful()) {
            return $this->decodeResponse(
                $retryResponse->json(),
                $retryResponse->status()
            );
        }

        throw JikanApiException::rateLimited($retryAfter);
    }

    /*
    |--------------------------------------------------------------------------
    | Rate Limit Delay
    |--------------------------------------------------------------------------
    */

    protected function rateLimit(): void
    {
        if ($this->rateDelay > 0) {
            usleep($this->rateDelay);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Anime Mapper
    |--------------------------------------------------------------------------
    */

    protected function mapAnime(array $item): array
    {
        $images = $item['images']['jpg'] ?? ($item['images']['webp'] ?? []);
        $trailerImages = $item['trailer']['images'] ?? [];

        $statusMap = [
            'Currently Airing' => 'Ongoing',
            'Finished Airing' => 'Completed',
            'Not yet aired' => 'Not Yet Aired',
        ];

        $synopsis = $this->cleanSynopsis($item['synopsis'] ?? null);

        $titleEnglish = $this->cleanNullable($item['title_english'] ?? null);
        $titleDefault = $this->cleanNullable($item['title'] ?? null);

        return [
            'mal_id' => $item['mal_id'] ?? null,
            'title' => $titleEnglish ?: $titleDefault ?: 'Unknown',
            'title_japanese' => $this->cleanNullable($item['title_japanese'] ?? null),
            'slug' => null,
            'description' => $synopsis,
            'type' => $this->cleanNullable($item['type'] ?? null),
            'status' => $statusMap[$item['status'] ?? ''] ?? $this->cleanNullable($item['status'] ?? null),
            'country' => 'JP',
            'season' => !empty($item['season'])
                ? ucfirst(strtolower((string) $item['season']))
                : null,
            'year' => isset($item['year']) && is_numeric($item['year'])
                ? (int) $item['year']
                : null,
            'rating' => $this->cleanNullable($item['rating'] ?? null),
            'score' => isset($item['score']) && is_numeric($item['score'])
                ? (float) $item['score']
                : null,
            'episodes_count' => isset($item['episodes']) && is_numeric($item['episodes'])
                ? (int) $item['episodes']
                : 0,
            'duration' => $this->parseDuration($item['duration'] ?? null),
            'source' => $this->cleanNullable($item['source'] ?? null),
            'studio' => $this->namesToString($item['studios'] ?? []),
            'producers' => $this->namesToString($item['producers'] ?? []),
            'licensors' => $this->namesToString($item['licensors'] ?? []),
            'thumbnail' => $images['large_image_url'] ?? $images['image_url'] ?? null,
            'banner' => $trailerImages['maximum_image_url']
                ?? $trailerImages['large_image_url']
                ?? null,
            'genres' => collect($item['genres'] ?? [])
                ->map(fn($genre) => [
                    'mal_id' => $genre['mal_id'] ?? null,
                    'name' => $this->cleanNullable($genre['name'] ?? null),
                ])
                ->filter(fn($genre) => !empty($genre['name']))
                ->values()
                ->toArray(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Episode Mapper
    |--------------------------------------------------------------------------
    */

    protected function mapEpisode(array $item): array
    {
        $images = $item['images']['jpg'] ?? [];

        $number = null;

        if (isset($item['episode']) && is_numeric($item['episode'])) {
            $number = (int) $item['episode'];
        } elseif (isset($item['mal_id']) && is_numeric($item['mal_id'])) {
            $number = (int) $item['mal_id'];
        }

        return [
            'number' => $number,
            'title' => $this->cleanNullable($item['title'] ?? null),
            'title_japanese' => $this->cleanNullable($item['title_japanese'] ?? null),
            'air_date' => $this->parseAirDate($item['aired'] ?? null),
            'duration' => $this->parseDuration($item['duration'] ?? null),
            'thumbnail' => $images['image_url'] ?? null,
            'synopsis' => $this->cleanNullable($item['synopsis'] ?? null),
            'filler' => (bool) ($item['filler'] ?? false),
            'recap' => (bool) ($item['recap'] ?? false),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function parseAirDate(?string $aired): ?string
    {
        if (!$aired) {
            return null;
        }

        try {
            return Carbon::parse($aired)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    protected function parseDuration(?string $duration): ?int
    {
        if (!$duration) {
            return null;
        }

        $duration = strtolower($duration);

        $hours = 0;
        $minutes = 0;
        $seconds = 0;

        if (preg_match('/(\d+)\s*hr/', $duration, $matches)) {
            $hours = (int) $matches[1];
        }

        if (preg_match('/(\d+)\s*min/', $duration, $matches)) {
            $minutes = (int) $matches[1];
        }

        if (preg_match('/(\d+)\s*sec/', $duration, $matches)) {
            $seconds = (int) $matches[1];
        }

        $totalSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;

        return $totalSeconds > 0 ? $totalSeconds : null;
    }

    protected function cleanSynopsis(?string $synopsis): ?string
    {
        if (!$synopsis) {
            return null;
        }

        $synopsis = preg_replace('/\s*\[Written by MAL Rewrite\]\s*/', '', $synopsis);

        return $this->cleanNullable($synopsis);
    }

    protected function cleanNullable(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    protected function namesToString(array $items): ?string
    {
        $value = collect($items)
            ->pluck('name')
            ->filter()
            ->implode(', ');

        return $value !== '' ? $value : null;
    }
}
