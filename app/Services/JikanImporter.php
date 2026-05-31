<?php

namespace App\Services;

use App\Models\Anime;

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

    public function upsertEpisodes(Anime $anime, array $episodes, bool $bulkInsert = false, bool $updateExisting = false): int
    {
        if ($updateExisting) {
            return $this->updateOrCreateEpisodes($anime, $episodes);
        }

        if ($bulkInsert) {
            return $this->bulkInsertEpisodes($anime, $episodes);
        }

        return $this->individualInsertEpisodes($anime, $episodes);
    }

    protected function updateOrCreateEpisodes(Anime $anime, array $episodes): int
    {
        $count = 0;
        $existingEpisodes = $anime->episodes()->get()->keyBy('number');

        foreach ($episodes as $ep) {
            $existing = $existingEpisodes->get($ep['number']);

            if ($existing) {
                $existing->update([
                    'title' => $ep['title'] ?: 'Episode '.$ep['number'],
                    'description' => $ep['synopsis'],
                    'thumbnail' => $ep['thumbnail'],
                    'air_date' => $ep['air_date'],
                    'duration' => $ep['duration'],
                ]);
            } else {
                $anime->episodes()->create([
                    'number' => $ep['number'],
                    'title' => $ep['title'] ?: 'Episode '.$ep['number'],
                    'description' => $ep['synopsis'],
                    'thumbnail' => $ep['thumbnail'],
                    'air_date' => $ep['air_date'],
                    'duration' => $ep['duration'],
                    'has_sub' => false,
                    'has_dub' => false,
                ]);
            }
            $count++;
        }

        return $count;
    }

    protected function bulkInsertEpisodes(Anime $anime, array $episodes): int
    {
        $existingNumbers = $anime->episodes()->pluck('number')->toArray();

        $newEpisodes = [];
        foreach ($episodes as $ep) {
            if (in_array($ep['number'], $existingNumbers)) {
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

        return count($newEpisodes);
    }

    protected function individualInsertEpisodes(Anime $anime, array $episodes): int
    {
        $count = 0;
        foreach ($episodes as $ep) {
            $existingEp = $anime->episodes()->where('number', $ep['number'])->first();
            if ($existingEp) {
                continue;
            }

            $anime->episodes()->create([
                'number' => $ep['number'],
                'title' => $ep['title'] ?: 'Episode '.$ep['number'],
                'description' => $ep['synopsis'],
                'thumbnail' => $ep['thumbnail'],
                'air_date' => $ep['air_date'],
                'duration' => $ep['duration'],
                'has_sub' => false,
                'has_dub' => false,
            ]);
            $count++;
        }

        return $count;
    }
}
