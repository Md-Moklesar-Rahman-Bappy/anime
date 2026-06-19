<?php

namespace App\Services;

use App\Models\Anime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class JikanImporter
{
    public function __construct(
        protected AnimeImportService $animeImport,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Genre Sync Proxy
    |--------------------------------------------------------------------------
    */

    public function syncGenres(array $genreData): array
    {
        return $this->animeImport->syncGenres($genreData);
    }

    /*
    |--------------------------------------------------------------------------
    | Anime Upsert Proxy
    |--------------------------------------------------------------------------
    */

    public function upsertAnime(array $data, array $genreIds): Anime
    {
        return $this->animeImport->upsertAnime($data, $genreIds);
    }

    /*
    |--------------------------------------------------------------------------
    | Upsert Episodes
    |--------------------------------------------------------------------------
    */

    public function upsertEpisodes(
        Anime $anime,
        array $episodes,
        bool $bulkInsert = false,
        bool $updateExisting = false
    ): int {
        if (empty($episodes)) {
            return 0;
        }

        return DB::transaction(function () use ($anime, $episodes, $bulkInsert, $updateExisting) {
            if ($updateExisting) {
                return $this->updateOrCreateEpisodes($anime, $episodes);
            }

            if ($bulkInsert) {
                return $this->bulkInsertEpisodes($anime, $episodes);
            }

            return $this->individualInsertEpisodes($anime, $episodes);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Update Existing or Create Missing Episodes
    |--------------------------------------------------------------------------
    */

    protected function updateOrCreateEpisodes(Anime $anime, array $episodes): int
    {
        $count = 0;
        $seenIncoming = [];

        $existingEpisodes = $anime->episodes()
            ->select('id', 'anime_id', 'number', 'title', 'description', 'thumbnail', 'air_date', 'duration')
            ->get()
            ->keyBy(fn($episode) => (string) $episode->number);

        foreach ($episodes as $ep) {
            $normalized = $this->normalizeEpisode($ep);

            if (!$normalized) {
                continue;
            }

            $numberKey = (string) $normalized['number'];

            /*
            |--------------------------------------------------------------------------
            | Prevent duplicate incoming payload numbers
            |--------------------------------------------------------------------------
            */
            if (isset($seenIncoming[$numberKey])) {
                continue;
            }

            $seenIncoming[$numberKey] = true;

            $existing = $existingEpisodes->get($numberKey);

            if ($existing) {
                $existing->update([
                    'title' => $normalized['title'],
                    'description' => $normalized['description'],
                    'thumbnail' => $normalized['thumbnail'],
                    'air_date' => $normalized['air_date'],
                    'duration' => $normalized['duration'],
                ]);
            } else {
                $anime->episodes()->create([
                    'number' => $normalized['number'],
                    'title' => $normalized['title'],
                    'description' => $normalized['description'],
                    'thumbnail' => $normalized['thumbnail'],
                    'air_date' => $normalized['air_date'],
                    'duration' => $normalized['duration'],
                    'has_sub' => false,
                    'has_dub' => false,
                ]);
            }

            $count++;
        }

        return $count;
    }

    /*
    |--------------------------------------------------------------------------
    | Bulk Insert New Episodes Only
    |--------------------------------------------------------------------------
    */

    protected function bulkInsertEpisodes(Anime $anime, array $episodes): int
    {
        $existingNumbers = $anime->episodes()
            ->pluck('number')
            ->map(fn($number) => (string) $number)
            ->toArray();

        $existingLookup = array_flip($existingNumbers);

        $newEpisodes = [];
        $seenIncoming = [];
        $now = now();

        foreach ($episodes as $ep) {
            $normalized = $this->normalizeEpisode($ep);

            if (!$normalized) {
                continue;
            }

            $numberKey = (string) $normalized['number'];

            if (isset($existingLookup[$numberKey]) || isset($seenIncoming[$numberKey])) {
                continue;
            }

            $seenIncoming[$numberKey] = true;

            $newEpisodes[] = [
                'anime_id' => $anime->id,
                'number' => $normalized['number'],
                'title' => $normalized['title'],
                'description' => $normalized['description'],
                'thumbnail' => $normalized['thumbnail'],
                'air_date' => $normalized['air_date'],
                'duration' => $normalized['duration'],
                'has_sub' => false,
                'has_dub' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($newEpisodes)) {
            foreach (array_chunk($newEpisodes, 200) as $chunk) {
                $anime->episodes()->insert($chunk);
            }
        }

        return count($newEpisodes);
    }

    /*
    |--------------------------------------------------------------------------
    | Insert New Episodes One-by-One
    |--------------------------------------------------------------------------
    */

    protected function individualInsertEpisodes(Anime $anime, array $episodes): int
    {
        $count = 0;

        $existingNumbers = $anime->episodes()
            ->pluck('number')
            ->map(fn($number) => (string) $number)
            ->toArray();

        $existingLookup = array_flip($existingNumbers);
        $seenIncoming = [];

        foreach ($episodes as $ep) {
            $normalized = $this->normalizeEpisode($ep);

            if (!$normalized) {
                continue;
            }

            $numberKey = (string) $normalized['number'];

            if (isset($existingLookup[$numberKey]) || isset($seenIncoming[$numberKey])) {
                continue;
            }

            $anime->episodes()->create([
                'number' => $normalized['number'],
                'title' => $normalized['title'],
                'description' => $normalized['description'],
                'thumbnail' => $normalized['thumbnail'],
                'air_date' => $normalized['air_date'],
                'duration' => $normalized['duration'],
                'has_sub' => false,
                'has_dub' => false,
            ]);

            $existingLookup[$numberKey] = true;
            $seenIncoming[$numberKey] = true;
            $count++;
        }

        return $count;
    }

    /*
    |--------------------------------------------------------------------------
    | Normalize Episode Payload
    |--------------------------------------------------------------------------
    */

    protected function normalizeEpisode(array $ep): ?array
    {
        try {
            $number = $ep['number'] ?? null;

            if ($number === null || $number === '' || !is_numeric($number)) {
                return null;
            }

            /*
            |--------------------------------------------------------------------------
            | Skip filler / recap episodes
            |--------------------------------------------------------------------------
            */
            if (($ep['filler'] ?? false) || ($ep['recap'] ?? false)) {
                return null;
            }

            $number = (int) $number;

            if ($number <= 0) {
                return null;
            }

            $title = trim((string) ($ep['title'] ?? ''));

            if ($title === '') {
                $title = 'Episode ' . $number;
            }

            $description = $this->cleanNullable($ep['synopsis'] ?? null);
            $thumbnail = $this->cleanNullable($ep['thumbnail'] ?? null);
            $airDate = $this->normalizeDate($ep['air_date'] ?? null);
            $duration = $this->normalizeDuration($ep['duration'] ?? null);

            return [
                'number' => $number,
                'title' => $title,
                'description' => $description,
                'thumbnail' => $thumbnail,
                'air_date' => $airDate,
                'duration' => $duration,
            ];
        } catch (\Throwable $e) {
            logger()->warning('Failed to normalize Jikan episode payload', [
                'episode_payload' => $ep,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function cleanNullable(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    protected function normalizeDuration(mixed $duration): ?int
    {
        if ($duration === null || $duration === '') {
            return null;
        }

        if (is_numeric($duration)) {
            return (int) $duration;
        }

        return null;
    }

    protected function normalizeDate(mixed $date): ?string
    {
        if (!$date) {
            return null;
        }

        try {
            return Carbon::parse($date)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
