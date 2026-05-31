<?php

namespace App\Jobs;

use App\Models\Anime;
use App\Models\Setting;
use App\Services\AnimeImportService;
use App\Services\JikanService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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
        $malId = $this->animeData['mal_id'];

        if (Anime::where('mal_id', $malId)->exists()) {
            return;
        }

        $data = $jikan->getAnime($malId);

        $episodes = $this->fetchEpisodes ? $jikan->getAllEpisodes($malId) : collect();

        $anime = $this->storeAnime($data, $episodes, $importer);

        if ($anime) {
            $anime->update(['episodes_count' => $anime->episodes()->count()]);
        }

        Setting::updateOrCreate(['key' => 'jikan_last_mal_id'], ['value' => $malId]);
    }

    protected function storeAnime(array $data, $episodes, AnimeImportService $importer): ?Anime
    {
        $genreIds = $importer->getGenreIdsUsingCache($data['genres']);

        $anime = $importer->upsertAnime($data, $genreIds);

        $existingEpisodeNumbers = $anime->episodes()->pluck('number')->toArray();

        $newEpisodes = [];
        foreach ($episodes as $ep) {
            if ($ep['filler'] || $ep['recap']) {
                continue;
            }
            if (in_array($ep['number'], $existingEpisodeNumbers)) {
                continue;
            }
            $newEpisodes[] = [
                'anime_id' => $anime->id,
                'number' => $ep['number'],
                'title' => $ep['title'] ?: 'Episode '.$ep['number'],
                'description' => $ep['synopsis'],
                'thumbnail' => $ep['thumbnail'],
                'air_date' => $ep['air_date'],
                'duration' => $ep['duration'],
                'has_sub' => false,
                'has_dub' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (! empty($newEpisodes)) {
            $anime->episodes()->insert($newEpisodes);
        }

        return $anime;
    }
}
