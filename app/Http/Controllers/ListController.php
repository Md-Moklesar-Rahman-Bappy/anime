<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesListing;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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

    public function azList(?string $letter = null)
    {
        try {
            $query = Anime::query()->with('genres');

            if ($letter && strtolower($letter) !== 'all') {
                $safeLetter = addcslashes($letter, '%_');
                $query->where('title', 'like', $safeLetter . '%');
            }

            $list = $query
                ->orderBy('title')
                ->paginate(24)
                ->withQueryString();

            $title = $letter
                ? "Anime starting with {$letter}"
                : 'All Anime';

            $genres = $this->getCachedGenres();

            return view('anime-list', [
                'animeList' => $list,
                'title' => $title,
                'genres' => $genres,
            ]);
        } catch (\Throwable $e) {
            Log::error('Anime A-Z list failed', [
                'letter' => $letter,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to load anime list.');
        }
    }

    public function searchAjax(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        if (mb_strlen($q) < 1) {
            return response()->json([
                'anime' => [],
                'episodes' => [],
            ]);
        }

        try {
            $cacheKey = 'anime_search_ajax_' . md5($q);

            $payload = Cache::remember($cacheKey, 60, function () use ($q) {
                $safeQ = '%' . addcslashes($q, '%_') . '%';

                $anime = Anime::query()
                    ->where('title', 'like', $safeQ)
                    ->select('id', 'title', 'slug', 'thumbnail', 'type', 'year')
                    ->orderByDesc('views')
                    ->take(6)
                    ->get()
                    ->map(fn ($a) => [
                        'id' => $a->id,
                        'title' => $a->title,
                        'slug' => $a->slug,
                        'thumbnail_url' => $a->thumbnail_url,
                        'type' => $a->type,
                        'year' => $a->year,
                        'url' => route('anime.detail', $a->slug),
                    ])
                    ->values();

                $episodes = Episode::query()
                    ->where('title', 'like', $safeQ)
                    ->with('anime:id,title,slug,thumbnail')
                    ->select('id', 'anime_id', 'number', 'title', 'thumbnail')
                    ->latest('created_at')
                    ->take(5)
                    ->get()
                    ->map(fn ($e) => [
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
                    ->filter(fn ($item) => !is_null($item['url']))
                    ->values();

                return [
                    'anime' => $anime,
                    'episodes' => $episodes,
                ];
            });

            return response()->json($payload);
        } catch (\Throwable $e) {
            Log::error('Anime AJAX search failed', [
                'query' => $q,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'anime' => [],
                'episodes' => [],
                'error' => 'Search failed.',
            ], 500);
        }
    }
}