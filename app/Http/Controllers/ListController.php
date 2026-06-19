<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesListing;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ListController extends Controller
{
    use HandlesListing;

    protected function modelClass(): string
    {
        return Anime::class;
    }

    protected function genreClass(): string
    {
        return Genre::class;
    }

    protected function cachePrefix(): string
    {
        return 'anime';
    }

    protected function listView(): string
    {
        return 'anime-list';
    }

    protected function listVariableName(): string
    {
        return 'animeList';
    }

    protected function itemLabel(): string
    {
        return 'Anime';
    }

    /*
    |--------------------------------------------------------------------------
    | A-Z LIST
    |--------------------------------------------------------------------------
    */

    public function azList(Request $request, ?string $letter = null)
    {
        try {
            $query = Anime::query()
                ->with('genres:id,name,slug');

            /*
            |--------------------------------------------------------------------------
            | Letter Filter
            |--------------------------------------------------------------------------
            */
            if ($letter && strtolower($letter) !== 'all') {
                $safeLetter = addcslashes($letter, '%_');
                $query->where('title', 'like', $safeLetter . '%');
            }

            $list = $query
                ->select('id', 'title', 'slug', 'thumbnail', 'type', 'year')
                ->orderBy('title')
                ->paginate(24)
                ->withQueryString();

            $title = $letter && strtolower($letter) !== 'all'
                ? "Anime starting with {$letter}"
                : 'All Anime';

            $genres = $this->getCachedGenres();

            return view('anime-list', [
                'animeList' => $list,
                'title' => $title,
                'genres' => $genres,
            ]);
        } catch (\Throwable $e) {

            $this->logError('Anime A-Z list failed', $e, [
                'letter' => $letter,
            ]);

            return $this->redirectError('Failed to load anime list.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX SEARCH
    |--------------------------------------------------------------------------
    */

    public function searchAjax(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        if (mb_strlen($q) < 1) {
            return $this->success([
                'anime' => [],
                'episodes' => [],
            ]);
        }

        try {
            $cacheKey = 'anime_search_ajax_' . md5($q);

            $payload = Cache::remember($cacheKey, 60, function () use ($q) {

                $safeQ = '%' . addcslashes($q, '%_') . '%';

                /*
                |--------------------------------------------------------------------------
                | Anime Results
                |--------------------------------------------------------------------------
                */
                $anime = Anime::query()
                    ->where('title', 'like', $safeQ)
                    ->select('id', 'title', 'slug', 'thumbnail', 'type', 'year')
                    ->orderByDesc('views')
                    ->take(6)
                    ->get()
                    ->map(fn($a) => [
                        'id' => $a->id,
                        'title' => $a->title,
                        'slug' => $a->slug,
                        'thumbnail_url' => $a->thumbnail_url,
                        'type' => $a->type,
                        'year' => $a->year,
                        'url' => route('anime.detail', $a->slug),
                    ])
                    ->values();

                /*
                |--------------------------------------------------------------------------
                | Episode Results
                |--------------------------------------------------------------------------
                */
                $episodes = Episode::query()
                    ->where('title', 'like', $safeQ)
                    ->with('anime:id,title,slug,thumbnail')
                    ->select('id', 'anime_id', 'number', 'title', 'thumbnail')
                    ->latest('created_at')
                    ->take(5)
                    ->get()
                    ->map(fn($e) => [
                        'id' => $e->id,
                        'title' => $e->title,
                        'number' => $e->number,
                        'thumbnail_url' => $e->thumbnail_url,
                        'anime_title' => $e->anime?->title,
                        'anime_slug' => $e->anime?->slug,
                        'url' => $e->anime
                            ? route('watch', [
                                'slug' => $e->anime->slug,
                                'ep' => $e->number,
                            ])
                            : null,
                    ])
                    ->filter(fn($item) => !is_null($item['url']))
                    ->values();

                return [
                    'anime' => $anime,
                    'episodes' => $episodes,
                ];
            });

            return $this->success($payload);
        } catch (\Throwable $e) {

            $this->logError('Anime AJAX search failed', $e, [
                'query' => $q,
            ]);

            return $this->error('Search failed.', 500);
        }
    }
}
