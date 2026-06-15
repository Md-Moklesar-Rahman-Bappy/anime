<?php

namespace App\Services;

use App\Models\Anime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JikanImporter
{
    public function __construct(
        protected AnimeImportService $animeImport,
    ) {}

    public function syncGenres(array $genreData): array
    {
        return $this->animeImport->syncGenres($genreData);
    }

    public function upsertAnime(array $data, array $genreIds): Anime
    {
        return $this->animeImport->upsertAnime($data, $genreIds);
    }

    /**
     * Insert or update episodes depending on chosen strategy.
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

    /**
     * Update existing episodes if found, otherwise create new ones.
     */
    protected function updateOrCreateEpisodes(Anime $anime, array $episodes): int
    {
        $count = 0;

        $existingEpisodes = $anime->episodes()
            ->get()
            ->keyBy(fn ($episode) => (string) $episode->number);

        foreach ($episodes as $ep) {
            $normalized = $this->normalizeEpisode($ep);

            if (!$normalized) {
                continue;
            }

            $key = (string) $normalized['number'];
            $existing = $existingEpisodes->get($key);

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

    /**
     * Bulk insert only new episodes for performance.
     */
    protected function bulkInsertEpisodes(Anime $anime, array $episodes): int
    {
        $existingNumbers = $anime->episodes()
            ->pluck('number')
            ->map(fn ($n) => (string) $n)
            ->toArray();

        $existingLookup = array_flip($existingNumbers);

        $newEpisodes = [];
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
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($newEpisodes)) {
            $anime->episodes()->insert($newEpisodes);
        }

        return count($newEpisodes);
    }

    /**
     * Insert episodes one-by-one, skipping existing episode numbers.
     */
    protected function individualInsertEpisodes(Anime $anime, array $episodes): int
    {
        $count = 0;

        $existingNumbers = $anime->episodes()
            ->pluck('number')
            ->map(fn ($n) => (string) $n)
            ->toArray();

        $existingLookup = array_flip($existingNumbers);

        foreach ($episodes as $ep) {
            $normalized = $this->normalizeEpisode($ep);

            if (!$normalized) {
                continue;
            }

            $numberKey = (string) $normalized['number'];

            if (isset($existingLookup[$numberKey])) {
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
            $count++;
        }

        return $count;
    }

    /**
     * Normalize and validate incoming Jikan episode data.
     * Returns null when episode should be skipped.
     */
    protected function normalizeEpisode(array $ep): ?array
    {
        try {
            $number = $ep['number'] ?? null;

            if ($number === null || $number === '' || !is_numeric($number)) {
                return null;
            }

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

            $description = $ep['synopsis'] ?? null;
            $thumbnail = $ep['thumbnail'] ?? null;
            $airDate = $ep['air_date'] ?? null;
            $duration = $ep['duration'] ?? null;

            if ($duration !== null && is_numeric($duration)) {
                $duration = (int) $duration;
            } else {
                $duration = null;
            }

            return [
                'number' => $number,
                'title' => $title,
                'description' => $description,
                'thumbnail' => $thumbnail,
                'air_date' => $airDate,
                'duration' => $duration,
            ];
        } catch (\Throwable $e) {
            Log::warning('Failed to normalize Jikan episode payload', [
                'episode_payload' => $ep,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}