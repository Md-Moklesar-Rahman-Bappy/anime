<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\Genre;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AnimeImportService
{
    protected const GENRES_CACHE_KEY = 'genres_all';
    protected const GENRES_CACHE_TTL = 3600;

    /*
    |--------------------------------------------------------------------------
    | Sync Genres
    |--------------------------------------------------------------------------
    */

    public function syncGenres(array $genreData): array
    {
        $allGenres = $this->cachedGenres();

        $genreIds = collect($genreData)
            ->map(function ($data) use (&$allGenres) {

                $name = trim((string) ($data['name'] ?? ''));

                if ($name === '') {
                    return null;
                }

                $malId = $data['mal_id'] ?? null;
                $slug = Str::slug($name);

                if (!$slug) {
                    return null;
                }

                /*
                |--------------------------------------------------------------------------
                | Find Existing Genre
                |--------------------------------------------------------------------------
                */
                $genre = null;

                if ($malId) {
                    $genre = $allGenres->firstWhere('mal_id', $malId);
                }

                if (!$genre) {
                    $genre = $allGenres->firstWhere('slug', $slug);
                }

                /*
                |--------------------------------------------------------------------------
                | Create Genre
                |--------------------------------------------------------------------------
                */
                if (!$genre) {
                    $genre = Genre::create([
                        'mal_id' => $malId,
                        'name' => $name,
                        'slug' => $this->uniqueGenreSlug($slug),
                    ]);

                    $allGenres->push($genre);

                    Cache::forget(self::GENRES_CACHE_KEY);

                    return $genre->id;
                }

                /*
                |--------------------------------------------------------------------------
                | Update Missing MAL ID / Name
                |--------------------------------------------------------------------------
                */
                $updates = [];

                if (!$genre->mal_id && $malId) {
                    $updates['mal_id'] = $malId;
                }

                if ($genre->name !== $name) {
                    $updates['name'] = $name;
                }

                if (!empty($updates)) {
                    $genre->update($updates);

                    Cache::forget(self::GENRES_CACHE_KEY);
                }

                return $genre->id;
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        return $genreIds;
    }

    /*
    |--------------------------------------------------------------------------
    | Upsert Anime
    |--------------------------------------------------------------------------
    */

    public function upsertAnime(array $data, array $genreIds): Anime
    {
        return DB::transaction(function () use ($data, $genreIds) {

            $malId = $data['mal_id'] ?? null;
            $title = trim((string) ($data['title'] ?? 'Unknown'));

            if ($title === '') {
                $title = 'Unknown';
            }

            $baseSlug = Str::slug($title) ?: 'anime';

            /*
            |--------------------------------------------------------------------------
            | Find Existing Anime Safely
            |--------------------------------------------------------------------------
            */
            $query = Anime::query();

            if ($malId) {
                $query->where('mal_id', $malId);
            } else {
                $query->where('slug', $baseSlug);
            }

            if ($malId) {
                $query->orWhere('slug', $baseSlug);
            }

            $anime = $query->first();

            /*
            |--------------------------------------------------------------------------
            | Existing Anime Update
            |--------------------------------------------------------------------------
            */
            if ($anime) {
                $anime->update([
                    'mal_id' => $malId ?: $anime->mal_id,
                    'title' => $title,
                    'title_japanese' => $data['title_japanese'] ?? $anime->title_japanese,
                    'description' => $data['description'] ?? $anime->description,
                    'type' => $data['type'] ?? $anime->type,
                    'status' => $data['status'] ?? $anime->status,
                    'country' => $data['country'] ?? $anime->country,
                    'season' => $data['season'] ?? $anime->season,
                    'year' => $data['year'] ?? $anime->year,
                    'age_rating' => $data['rating'] ?? $anime->age_rating,
                    'score' => $data['score'] ?? $anime->score,
                    'episodes_count' => $data['episodes_count'] ?? $anime->episodes_count,
                    'duration' => $data['duration'] ?? $anime->duration,
                    'source' => $data['source'] ?? $anime->source,
                    'studio' => $data['studio'] ?? $anime->studio,
                    'producers' => $data['producers'] ?? $anime->producers,
                    'licensors' => $data['licensors'] ?? $anime->licensors,
                    'thumbnail' => !empty($data['thumbnail']) ? $data['thumbnail'] : $anime->thumbnail,
                    'banner' => !empty($data['banner']) ? $data['banner'] : $anime->banner,
                    'jikan_synced_at' => now(),
                ]);

                $anime->genres()->sync($genreIds);

                return $anime;
            }

            /*
            |--------------------------------------------------------------------------
            | New Anime Create
            |--------------------------------------------------------------------------
            */
            $anime = Anime::create([
                'mal_id' => $malId,
                'title' => $title,
                'title_japanese' => $data['title_japanese'] ?? null,
                'slug' => $this->uniqueAnimeSlug($baseSlug),
                'description' => $data['description'] ?? null,
                'type' => $data['type'] ?? null,
                'status' => $data['status'] ?? null,
                'country' => $data['country'] ?? null,
                'season' => $data['season'] ?? null,
                'year' => $data['year'] ?? null,
                'age_rating' => $data['rating'] ?? null,
                'score' => $data['score'] ?? null,
                'episodes_count' => $data['episodes_count'] ?? 0,
                'duration' => $data['duration'] ?? null,
                'source' => $data['source'] ?? null,
                'studio' => $data['studio'] ?? null,
                'producers' => $data['producers'] ?? null,
                'licensors' => $data['licensors'] ?? null,
                'thumbnail' => $data['thumbnail'] ?? null,
                'banner' => $data['banner'] ?? null,
                'views' => 0,
                'featured' => false,
                'featured_order' => 0,
                'jikan_synced_at' => now(),
            ]);

            $anime->genres()->sync($genreIds);

            return $anime;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Cached Genres
    |--------------------------------------------------------------------------
    */

    protected function cachedGenres(): Collection
    {
        return Cache::remember(
            self::GENRES_CACHE_KEY,
            self::GENRES_CACHE_TTL,
            fn() => Genre::select('id', 'name', 'slug', 'mal_id')->get()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Unique Anime Slug
    |--------------------------------------------------------------------------
    */

    protected function uniqueAnimeSlug(string $baseSlug): string
    {
        $baseSlug = $baseSlug ?: 'anime';
        $slug = $baseSlug;
        $counter = 1;

        while (Anime::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /*
    |--------------------------------------------------------------------------
    | Unique Genre Slug
    |--------------------------------------------------------------------------
    */

    protected function uniqueGenreSlug(string $baseSlug): string
    {
        $baseSlug = $baseSlug ?: 'genre';
        $slug = $baseSlug;
        $counter = 1;

        while (Genre::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
