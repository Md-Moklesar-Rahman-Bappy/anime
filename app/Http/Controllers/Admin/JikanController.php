<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Genre;
use App\Models\Setting;
use App\Services\JikanService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class JikanController extends Controller
{
    protected JikanService $jikan;

    public function __construct(JikanService $jikan)
    {
        $this->jikan = $jikan;
    }

    public function searchForm()
    {
        $lastMalId = Setting::where('key', 'jikan_last_mal_id')->value('value');
        $totalImported = Anime::whereNotNull('mal_id')->count();
        $importProgress = $lastMalId ? "Resume from MAL #{$lastMalId}" : null;

        return view('admin.jikan.search', compact('lastMalId', 'totalImported', 'importProgress'));
    }

    public function search(Request $request)
    {
        $request->validate(['q' => 'required|string|max:255']);

        $page = $request->integer('page', 1);
        $results = $this->jikan->searchAnime($request->q, $page);
        $pagination = $this->jikan->searchPagination($request->q, $page);

        $existingMalIds = Anime::whereIn('mal_id', $results->pluck('mal_id')->filter())
            ->pluck('mal_id')
            ->toArray();

        $lastMalId = Setting::where('key', 'jikan_last_mal_id')->value('value');
        $totalImported = Anime::whereNotNull('mal_id')->count();

        return view('admin.jikan.search', compact(
            'results', 'pagination', 'existingMalIds', 'lastMalId', 'totalImported'
        ) + [
            'query' => $request->q,
            'currentPage' => $page,
        ]);
    }

    public function preview(int $malId)
    {
        $anime = $this->jikan->getAnime($malId);

        if (! $anime) {
            return redirect()->route('admin.jikan.search')
                ->with('error', 'Anime not found on MyAnimeList.');
        }

        $episodes = $this->jikan->getAnimeEpisodes($malId);
        $alreadyImported = Anime::where('mal_id', $malId)->exists();

        return view('admin.jikan.preview', compact('anime', 'episodes', 'alreadyImported'));
    }

    public function import(int $malId)
    {
        if (Anime::where('mal_id', $malId)->exists()) {
            return redirect()->route('admin.jikan.search')
                ->with('error', 'This anime has already been imported.');
        }

        $data = $this->jikan->getAnime($malId);

        if (! $data) {
            return redirect()->route('admin.jikan.search')
                ->with('error', 'Failed to fetch anime data from MyAnimeList.');
        }

        $episodeData = $this->jikan->getAnimeEpisodes($malId);

        $genreIds = [];
        foreach ($data['genres'] as $genreData) {
            $slug = Str::slug($genreData['name']);
            $genre = Genre::where('mal_id', $genreData['mal_id'])->orWhere('slug', $slug)->first();
            if (! $genre) {
                $genre = Genre::create([
                    'mal_id' => $genreData['mal_id'],
                    'name' => $genreData['name'],
                    'slug' => $slug,
                ]);
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
                'rating' => $data['rating'],
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
            $anime = $existing;
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
                'rating' => $data['rating'],
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

        foreach ($episodeData as $ep) {
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
        }

        $action = $existing ? 'Updated' : 'Imported';

        return redirect()->route('admin.anime.index')
            ->with('success', "{$action} \"{$anime->title}\" with {$anime->episodes()->count()} episodes from MAL.");
    }

    public function batchImport(Request $request)
    {
        $request->validate([
            'batch_size' => 'integer|min:1|max:50',
            'with_episodes' => 'boolean',
        ]);

        $batchSize = $request->integer('batch_size', 10);
        $fetchEpisodes = $request->boolean('with_episodes', false);
        $resumeMalId = Setting::where('key', 'jikan_last_mal_id')->value('value');
        $page = 1;
        $imported = 0;
        $skipped = 0;
        $errors = 0;

        while ($imported < $batchSize) {
            $results = $this->jikan->browseAnime($page);

            if ($results->isEmpty()) {
                break;
            }

            foreach ($results as $data) {
                if ($imported >= $batchSize) {
                    break 2;
                }

                $malId = $data['mal_id'];

                if ($resumeMalId && $malId <= (int) $resumeMalId) {
                    $skipped++;

                    continue;
                }

                if (Anime::where('mal_id', $malId)->exists()) {
                    $skipped++;

                    continue;
                }

                try {
                    $episodes = $fetchEpisodes ? $this->jikan->getAllEpisodes($malId) : collect();
                    $this->storeAnime($data, $episodes);
                    Setting::updateOrCreate(['key' => 'jikan_last_mal_id'], ['value' => $malId]);
                    $imported++;
                } catch (\Exception $e) {
                    $errors++;
                }
            }

            $pagination = $this->jikan->browsePagination($page + 1);
            if (! ($pagination['has_next_page'] ?? false)) {
                break;
            }
            $page++;
        }

        if (! $resumeMalId && $imported === 0) {
            Setting::where('key', 'jikan_last_mal_id')->delete();
        }

        return redirect()->route('admin.jikan.search')
            ->with('success', "Batch import complete. Imported: {$imported}, Skipped: {$skipped}, Errors: {$errors}.");
    }

    public function resetProgress()
    {
        Setting::where('key', 'jikan_last_mal_id')->delete();

        return redirect()->route('admin.jikan.search')
            ->with('success', 'Import progress has been reset.');
    }

    protected function storeAnime(array $data, $episodes): Anime
    {
        $genreIds = [];
        foreach ($data['genres'] as $genreData) {
            $slug = Str::slug($genreData['name']);
            $genre = Genre::where('mal_id', $genreData['mal_id'])->orWhere('slug', $slug)->first();
            if (! $genre) {
                $genre = Genre::create([
                    'mal_id' => $genreData['mal_id'],
                    'name' => $genreData['name'],
                    'slug' => $slug,
                ]);
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
                'rating' => $data['rating'],
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
            $anime = $existing;
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
                'rating' => $data['rating'],
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
        }

        return $anime;
    }
}
