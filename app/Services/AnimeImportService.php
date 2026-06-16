<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\Genre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AnimeImportService
{
    public function syncGenres(array $genreData): array
    {
        $allGenres = Cache::remember('genres_all', 3600, fn () => Genre::all());

        return collect($genreData)->map(function ($data) use (&$allGenres) {

            $slug = Str::slug($data['name'] ?? '');

            if (!$slug) {
                return null;
            }

            $genre = $allGenres->firstWhere('mal_id', $data['mal_id'] ?? null)
                ?? $allGenres->firstWhere('slug', $slug);

            if (!$genre) {
                $genre = Genre::create([
                    'mal_id' => $data['mal_id'] ?? null,
                    'name' => $data['name'] ?? 'Unknown',
                    'slug' => $slug,
                ]);

                $allGenres->push($genre);

            } elseif (!$genre->mal_id && !empty($data['mal_id'])) {
                $genre->update(['mal_id' => $data['mal_id']]);
            }

            return $genre->id;

        })->filter()->values()->toArray();
    }

    public function upsertAnime(array $data, array $genreIds): Anime
    {
        return DB::transaction(function () use ($data, $genreIds) {

            $title = $data['title'] ?? 'Unknown';
            $slug = Str::slug($title);

            $existing = Anime::where('mal_id', $data['mal_id'] ?? null)
                ->orWhere('slug', $slug)
                ->first();

            if ($existing) {

                $existing->update([
                    'mal_id' => $data['mal_id'] ?? $existing->mal_id,
                    'title' => $title,
                    'title_japanese' => $data['title_japanese'] ?? null,
                    'description' => $data['description'] ?? null,
                    'type' => $data['type'] ?? null,
                    'status' => $data['status'] ?? null,
                    'country' => $data['country'] ?? null,
                    'season' => $data['season'] ?? null,
                    'year' => $data['year'] ?? null,
                    'age_rating' => $data['rating'] ?? null,
                    'score' => $data['score'] ?? null,
                    'episodes_count' => $data['episodes_count'] ?? null,
                    'duration' => $data['duration'] ?? null,
                    'source' => $data['source'] ?? null,
                    'studio' => $data['studio'] ?? null,
                    'producers' => $data['producers'] ?? null,
                    'licensors' => $data['licensors'] ?? null,
                    'thumbnail' => $data['thumbnail'] ?: $existing->thumbnail,
                    'banner' => $data['banner'] ?: $existing->banner,
                    'jikan_synced_at' => now(),
                ]);

                $existing->genres()->sync($genreIds);

                return $existing;
            }

            // ✅ Optimized slug uniqueness
            $uniqueSlug = "{$slug}-" . substr(md5(uniqid()), 0, 6);

            $anime = Anime::create([
                'mal_id' => $data['mal_id'] ?? null,
                'title' => $title,
                'title_japanese' => $data['title_japanese'] ?? null,
                'slug' => $uniqueSlug,
                'description' => $data['description'] ?? null,
                'type' => $data['type'] ?? null,
                'status' => $data['status'] ?? null,
                'country' => $data['country'] ?? null,
                'season' => $data['season'] ?? null,
                'year' => $data['year'] ?? null,
                'age_rating' => $data['rating'] ?? null,
                'score' => $data['score'] ?? null,
                'episodes_count' => $data['episodes_count'] ?? null,
                'duration' => $data['duration'] ?? null,
                'source' => $data['source'] ?? null,
                'studio' => $data['studio'] ?? null,
                'producers' => $data['producers'] ?? null,
                'licensors' => $data['licensors'] ?? null,
                'thumbnail' => $data['thumbnail'] ?? null,
                'banner' => $data['banner'] ?? null,
                'jikan_synced_at' => now(),
            ]);

            $anime->genres()->sync($genreIds);

            return $anime;
        });
    }
}