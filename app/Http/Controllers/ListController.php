<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ListController extends Controller
{
    protected function getCachedGenres()
    {
        return Cache::remember('genres_list', 1800, fn() => Genre::all());
    }

    public function newest()
    {
        $animeList = Anime::latest()->paginate(24);
        $title = 'Newest Anime';
        $genres = $this->getCachedGenres();

        return view('anime-list', compact('animeList', 'title', 'genres'));
    }

    public function updated()
    {
        $recentIds = Cache::remember('recently_updated_anime_ids', 300, function () {
            return Episode::where('created_at', '>=', now()->subWeek())
                ->distinct()->pluck('anime_id');
        });

        $animeList = Anime::whereIn('id', $recentIds)->latest()->paginate(24);
        $title = 'Recently Updated';
        $genres = $this->getCachedGenres();

        return view('anime-list', compact('animeList', 'title', 'genres'));
    }

    public function ongoing()
    {
        $animeList = Anime::where('status', 'Ongoing')->latest()->paginate(24);
        $title = 'Ongoing Anime';
        $genres = $this->getCachedGenres();

        return view('anime-list', compact('animeList', 'title', 'genres'));
    }

    public function trending()
    {
        $animeList = Anime::orderBy('views', 'desc')->paginate(24);
        $title = 'Trending Anime';
        $genres = $this->getCachedGenres();

        return view('anime-list', compact('animeList', 'title', 'genres'));
    }

    public function azList($letter = null)
    {
        $query = Anime::query();
        if ($letter && $letter !== 'all') {
            $query->where('title', 'like', $letter.'%');
        }
        $animeList = $query->orderBy('title')->paginate(24);
        $title = $letter ? "Anime starting with $letter" : 'All Anime';
        $genres = $this->getCachedGenres();

        return view('anime-list', compact('animeList', 'title', 'genres'));
    }

    public function filter(Request $request)
    {
        $query = Anime::query();

        if ($request->q) {
            $query->where('title', 'like', "%{$request->q}%");
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->year) {
            if (str_ends_with($request->year, 's')) {
                $decade = (int) substr($request->year, 0, -1);
                $query->whereBetween('year', [$decade * 10, $decade * 10 + 9]);
            } else {
                $query->where('year', $request->year);
            }
        }

        if ($request->season) {
            $query->where('season', $request->season);
        }

        if ($request->country) {
            $query->where('country', $request->country);
        }

        if ($request->rating) {
            $query->where('rating', $request->rating);
        }

        if ($request->genres) {
            $genreSlugs = (array) $request->genres;
            $query->whereHas('genres', function ($q) use ($genreSlugs) {
                $q->whereIn('genres.slug', $genreSlugs);
            });
        }

        switch ($request->sort) {
            case 'updated':
                $query->latest('updated_at');
                break;
            case 'added':
                $query->latest('created_at');
                break;
            case 'views':
                $query->orderBy('views', 'desc');
                break;
            case 'score':
                $query->orderBy('score', 'desc');
                break;
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            case 'name':
                $query->orderBy('title');
                break;
            case 'episodes':
                $query->orderBy('episodes_count', 'desc');
                break;
            case 'release':
                $query->orderBy('year', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        $animeList = $query->paginate(24)->withQueryString();
        $title = 'Filter Results';
        $genres = $this->getCachedGenres();

        return view('anime-list', compact('animeList', 'title', 'genres'));
    }

    public function searchAjax(Request $request)
    {
        $q = $request->input('q', '');

        if (strlen($q) < 1) {
            return response()->json(['anime' => [], 'episodes' => []]);
        }

        $anime = Anime::where('title', 'like', "%{$q}%")
            ->select('id', 'title', 'slug', 'thumbnail', 'type', 'year')
            ->orderBy('views', 'desc')
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
            ]);

        $episodes = Episode::where('title', 'like', "%{$q}%")
            ->with('anime:id,title,slug,thumbnail')
            ->select('id', 'anime_id', 'number', 'title', 'thumbnail')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'number' => $e->number,
                'thumbnail_url' => $e->thumbnail_url,
                'anime_title' => $e->anime->title,
                'anime_slug' => $e->anime->slug,
                'url' => route('watch', ['slug' => $e->anime->slug, 'ep' => $e->number]),
            ]);

        return response()->json(['anime' => $anime, 'episodes' => $episodes]);
    }
}
