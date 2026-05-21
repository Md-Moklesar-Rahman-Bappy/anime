<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\Genre;
use Illuminate\Support\Str;

class JikanImporter
{
    public function syncGenres(array $genreData): array
    {
        $genreIds = [];

        foreach ($genreData as $data) {
            $slug = Str::slug($data['name']);
            $genre = Genre::where('mal_id', $data['mal_id'])
                ->orWhere('slug', $slug)
                ->first();

            if (! $genre) {
                $genre = Genre::create([
                    'mal_id' => $data['mal_id'],
                    'name' => $data['name'],
                    'slug' => $slug,
                ]);
            } elseif (! $genre->mal_id) {
                $genre->update(['mal_id' => $data['mal_id']]);
            }

            $genreIds[] = $genre->id;
        }

        return $genreIds;
    }

    public function upsertAnime(array $data, array $genreIds): Anime
    {
        $slug = Str::slug($data['title']);

        $existing = Anime::where('slug', $slug)
            ->orWhere('mal_id', $data['mal_id'])
            ->first();

        if ($existing) {
            $existing->update([
                'mal_id' => $data['mal_id'],
                'title' => $data['title'],
                'title_japanese' => $data['title_japanese'],
                'description' => $data['description'],
                'type' => $data['type'],
                'status' => $data['status'],
                'country' => $data['country'],
                'season' => $data['season'],
                'year' => $data['year'],
                'age_rating' => $data['rating'],
                'score' => $data['score'],
                'episodes_count' => $data['episodes_count'],
                'duration' => $data['duration'],
                'source' => $data['source'],
                'studio' => $data['studio'],
                'producers' => $data['producers'],
                'licensors' => $data['licensors'],
                'thumbnail' => $data['thumbnail'] ?: $existing->thumbnail,
                'banner' => $data['banner'] ?: $existing->banner,
                'jikan_synced_at' => now(),
            ]);
            $existing->genres()->sync($genreIds);

            return $existing;
        }

        $counter = 1;
        $originalSlug = $slug;
        while (Anime::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter++;
        }

        $anime = Anime::create([
            'mal_id' => $data['mal_id'],
            'title' => $data['title'],
            'title_japanese' => $data['title_japanese'],
            'slug' => $slug,
            'description' => $data['description'],
            'type' => $data['type'],
            'status' => $data['status'],
            'country' => $data['country'],
            'season' => $data['season'],
            'year' => $data['year'],
            'age_rating' => $data['rating'],
            'score' => $data['score'],
            'episodes_count' => $data['episodes_count'],
            'duration' => $data['duration'],
            'source' => $data['source'],
            'studio' => $data['studio'],
            'producers' => $data['producers'],
            'licensors' => $data['licensors'],
            'thumbnail' => $data['thumbnail'],
            'banner' => $data['banner'],
            'jikan_synced_at' => now(),
        ]);

        $anime->genres()->sync($genreIds);

        return $anime;
    }

    public function upsertEpisodes(Anime $anime, iterable $episodes, bool $bulkInsert = false): int
    {
        if ($bulkInsert) {
            return $this->bulkInsertEpisodes($anime, $episodes);
        }

        return $this->individualInsertEpisodes($anime, $episodes);
    }

    protected function bulkInsertEpisodes(Anime $anime, iterable $episodes): int
    {
        $existingNumbers = $anime->episodes()->pluck('number')->toArray();

        $newEpisodes = [];
        foreach ($episodes as $ep) {
            if ($ep['filler'] || $ep['recap']) {
                continue;
            }
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

    protected function individualInsertEpisodes(Anime $anime, iterable $episodes): int
    {
        $count = 0;
        foreach ($episodes as $ep) {
            if ($ep['filler'] || $ep['recap']) {
                continue;
            }

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
