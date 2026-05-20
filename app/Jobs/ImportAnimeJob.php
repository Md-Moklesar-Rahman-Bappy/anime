<?php

namespace App\Jobs;

use App\Models\Anime;
use App\Models\Genre;
use App\Models\Setting;
use App\Services\JikanService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class ImportAnimeJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public int $tries = 3;

    public function __construct(
        public array $animeData,
        public bool $fetchEpisodes = false,
    ) {}

    public function handle(JikanService $jikan): void
    {
        $malId = $this->animeData['mal_id'];

        if (Anime::where('mal_id', $malId)->exists()) {
            return;
        }

        $data = $jikan->getAnime($malId);

        if ($jikan->lastError || ! $data) {
            \Log::warning("Jikan import failed for MAL #{$malId}: ".$jikan->lastError);
            return;
        }

        $episodes = $this->fetchEpisodes ? $jikan->getAllEpisodes($malId) : collect();

        $this->storeAnime($data, $episodes);

        Setting::updateOrCreate(['key' => 'jikan_last_mal_id'], ['value' => $malId]);
    }

    protected function storeAnime(array $data, $episodes): void
    {
        $allGenres = Genre::all();

        $genreIds = [];
        foreach ($data['genres'] as $genreData) {
            $genre = $allGenres->firstWhere('mal_id', $genreData['mal_id'])
                ?? $allGenres->firstWhere('slug', Str::slug($genreData['name']));

            if (! $genre) {
                $genre = Genre::create([
                    'mal_id' => $genreData['mal_id'],
                    'name' => $genreData['name'],
                    'slug' => Str::slug($genreData['name']),
                ]);
                $allGenres->push($genre);
            } elseif (! $genre->mal_id) {
                $genre->update(['mal_id' => $genreData['mal_id']]);
            }
            $genreIds[] = $genre->id;
        }

        $slug = Str::slug($data['title']);

        $existing = Anime::where('slug', $slug)
            ->orWhere('mal_id', $data['mal_id'])
            ->first();

        if ($existing) {
            $existing->update([
                'mal_id' => $data['mal_id'],
                'title' => $data['title'],
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
        } else {
            $counter = 1;
            $originalSlug = $slug;
            while (Anime::where('slug', $slug)->exists()) {
                $slug = $originalSlug.'-'.$counter++;
            }

            $anime = Anime::create([
                'mal_id' => $data['mal_id'],
                'title' => $data['title'],
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
        }

        $anime = $existing ?? $anime;

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
    }
}
