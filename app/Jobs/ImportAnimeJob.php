<?php

namespace App\Jobs;

use App\Models\Anime;
use App\Models\Setting;
use App\Services\AnimeImportService;
use App\Services\JikanService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

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
        $malId = $this->animeData['mal_id'] ?? null;

        if (!$malId) {
            return;
        }

        try {
            DB::transaction(function () use ($jikan, $importer, $malId) {

                // ✅ Safe deduplication inside transaction
                if (Anime::where('mal_id', $malId)->lockForUpdate()->exists()) {
                    return;
                }

                $data = $jikan->getAnime($malId);

                if (!$data) {
                    return;
                }

                $episodes = collect();

                if ($this->fetchEpisodes) {
                    $episodes = collect($jikan->getAllEpisodes($malId) ?? []);
                }

                $anime = $this->storeAnime($data, $episodes, $importer);

                if ($anime) {
                    $anime->update([
                        'episodes_count' => $anime->episodes()->count(),
                    ]);
                }

                Setting::updateOrCreate(
                    ['key' => 'jikan_last_mal_id'],
                    ['value' => $malId]
                );
            });

        } catch (\Throwable $e) {

            logger()->error('Anime import job failed', [
                'mal_id' => $malId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function storeAnime($data, $episodes, AnimeImportService $importer): ?Anime
    {
        try {
            $genreIds = $importer->syncGenres($data['genres'] ?? []);

            $anime = $importer->upsertAnime($data, $genreIds);

            $existingEpisodes = $anime->episodes()
                ->pluck('number')
                ->all();

            $newEpisodes = [];

            foreach ($episodes as $ep) {

                $number = (int) ($ep['number'] ?? 0);

                if (
                    !$number ||
                    ($ep['filler'] ?? false) ||
                    ($ep['recap'] ?? false) ||
                    in_array($number, $existingEpisodes, true)
                ) {
                    continue;
                }

                $newEpisodes[] = [
                    'anime_id'    => $anime->id,
                    'number'      => $number,
                    'title'       => $ep['title'] ?: "Episode {$number}",
                    'description' => $ep['synopsis'] ?? null,
                    'thumbnail'   => $ep['thumbnail'] ?? null,
                    'air_date'    => $ep['air_date'] ?? null,
                    'duration'    => $ep['duration'] ?? null,
                    'has_sub'     => false,
                    'has_dub'     => false,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Batch insert for performance
            |--------------------------------------------------------------------------
            */
            if (!empty($newEpisodes)) {
                foreach (array_chunk($newEpisodes, 200) as $chunk) {
                    $anime->episodes()->insert($chunk);
                }
            }

            return $anime;

        } catch (\Throwable $e) {

            logger()->error('Store anime failed', [
                'mal_id' => $data['mal_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}