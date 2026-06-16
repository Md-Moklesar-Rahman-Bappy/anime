<?php

namespace App\Jobs;

use App\Models\Anime;
use App\Models\Setting;
use App\Services\AnimeImportService;
use App\Services\JikanService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ImportAnimeJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;
    public int $tries = 3;

    public function __construct(
        public array $animeData,
        public bool $fetchEpisodes = false,
    ) {}

    public function handle(JikanService $jikan, AnimeImportService $importer): void
    {
        try {
            $malId = $this->animeData['mal_id'] ?? null;

            if (!$malId) {
                return;
            }

            // ✅ Prevent duplicates safely
            if (Anime::where('mal_id', $malId)->exists()) {
                return;
            }

            // ✅ Fetch anime
            $data = $jikan->getAnime($malId);

            $episodes = collect();

            if ($this->fetchEpisodes) {
                $episodes = $jikan->getAllEpisodes($malId) ?? collect();
            }

            $anime = $this->storeAnime($data, $episodes, $importer);

            if ($anime) {
                $anime->update([
                    'episodes_count' => $anime->episodes()->count()
                ]);
            }

            // ✅ Save progress
            Setting::updateOrCreate(
                ['key' => 'jikan_last_mal_id'],
                ['value' => $malId]
            );

        } catch (\Throwable $e) {
            Log::error('Anime import job failed', [
                'mal_id' => $this->animeData['mal_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            throw $e; // ✅ Allow retry
        }
    }

    protected function storeAnime(array $data, $episodes, AnimeImportService $importer): ?Anime
    {
        try {
            $genreIds = $importer->getGenreIdsUsingCache($data['genres'] ?? []);

            $anime = $importer->upsertAnime($data, $genreIds);

            $existingEpisodes = $anime->episodes()
                ->pluck('number')
                ->toArray();

            $newEpisodes = [];

            foreach ($episodes as $ep) {
                // ✅ Validation of external data
                if (
                    empty($ep['number']) ||
                    $ep['filler'] ||
                    $ep['recap'] ||
                    in_array($ep['number'], $existingEpisodes)
                ) {
                    continue;
                }

                $newEpisodes[] = [
                    'anime_id' => $anime->id,
                    'number' => (int) $ep['number'],
                    'title' => $ep['title'] ?: 'Episode ' . $ep['number'],
                    'description' => $ep['synopsis'] ?? null,
                    'thumbnail' => $ep['thumbnail'] ?? null,
                    'air_date' => $ep['air_date'] ?? null,
                    'duration' => $ep['duration'] ?? null,
                    'has_sub' => false,
                    'has_dub' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($newEpisodes)) {
                $anime->episodes()->insert($newEpisodes);
            }

            return $anime;

        } catch (\Throwable $e) {
            Log::error('Store anime failed', [
                'mal_id' => $data['mal_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}