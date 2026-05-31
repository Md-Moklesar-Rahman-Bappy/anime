<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\Genre;
use Illuminate\Support\Str;

class AnimeImportService
{
    public function syncGenres(array $genreData): array
    {
        $allGenres = Genre::all();

        return collect($genreData)->map(function ($data) use ($allGenres) {
            $slug = Str::slug($data['name']);
            $genre = $allGenres->firstWhere('mal_id', $data['mal_id'])
                ?? $allGenres->firstWhere('slug', $slug);

            if (!$genre) {
                $genre = Genre::create([
                    'mal_id' => $data['mal_id'],
                    'name' => $data['name'],
                    'slug' => $slug,
                ]);
                $allGenres->push($genre);
            } elseif (!$genre->mal_id) {
                $genre->update(['mal_id' => $data['mal_id']]);
            }

            return $genre->id;
        })->toArray();
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
            $slug = $originalSlug . '-' . $counter++;
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
}
