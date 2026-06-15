<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesListing;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Genre;
use Illuminate\Http\Request;

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

    public function azList($letter = null)
    {
        $query = Anime::query();
        if ($letter && $letter !== 'all') {
            $query->where('title', 'like', $letter.'%');
        }
        $list = $query->orderBy('title')->paginate(24);
        $title = $letter ? "Anime starting with $letter" : 'All Anime';
        $genres = $this->getCachedGenres();

        return view('anime-list', ['animeList' => $list, 'title' => $title, 'genres' => $genres]);
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
