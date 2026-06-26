<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Manga;
use App\Models\MangaGenre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MangaListController extends Controller
{
    protected function getCachedGenres()
    {
        return Cache::remember('manga_genres_list', 1800, fn() => MangaGenre::all());
    }

    public function index()
    {
        $mangaList = Manga::latest()->paginate(24);
        $title = 'All Manga';
        $genres = $this->getCachedGenres();

        return view('manga-list', compact('mangaList', 'title', 'genres'));
    }

    public function newest()
    {
        $mangaList = Manga::latest()->paginate(24);
        $title = 'Newest Manga';
        $genres = $this->getCachedGenres();

        return view('manga-list', compact('mangaList', 'title', 'genres'));
    }

    public function updated()
    {
        $recentIds = Cache::remember('recently_updated_manga_ids', 300, function () {
            return Chapter::where('created_at', '>=', now()->subWeek())
                ->distinct()->pluck('manga_id');
        });

        $mangaList = Manga::whereIn('id', $recentIds)->latest()->paginate(24);
        $title = 'Recently Updated';
        $genres = $this->getCachedGenres();

        return view('manga-list', compact('mangaList', 'title', 'genres'));
    }

    public function ongoing()
    {
        $mangaList = Manga::where('status', 'Ongoing')->latest()->paginate(24);
        $title = 'Ongoing Manga';
        $genres = $this->getCachedGenres();

        return view('manga-list', compact('mangaList', 'title', 'genres'));
    }

    public function trending()
    {
        $mangaList = Manga::orderBy('views', 'desc')->paginate(24);
        $title = 'Trending Manga';
        $genres = $this->getCachedGenres();

        return view('manga-list', compact('mangaList', 'title', 'genres'));
    }

    public function completed()
    {
        $mangaList = Manga::where('status', 'Completed')->latest()->paginate(24);
        $title = 'Completed Manga';
        $genres = $this->getCachedGenres();

        return view('manga-list', compact('mangaList', 'title', 'genres'));
    }

    public function filter(Request $request)
    {
        $query = Manga::query();

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

        if ($request->genres) {
            $genreIds = MangaGenre::whereIn('slug', (array) $request->genres)->pluck('id');
            $query->whereHas('genres', function ($q) use ($genreIds) {
                $q->whereIn('manga_genre_relation.manga_genre_id', $genreIds);
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
            case 'chapters':
                $query->orderBy('chapters_count', 'desc');
                break;
            case 'release':
                $query->orderBy('year', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        $mangaList = $query->paginate(24)->withQueryString();
        $title = 'Filter Results';
        $genres = $this->getCachedGenres();

        return view('manga-list', compact('mangaList', 'title', 'genres'));
    }
}
