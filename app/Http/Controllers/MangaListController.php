<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesListing;
use App\Models\Manga;
use App\Models\MangaGenre;
use Illuminate\Http\Request;

class MangaListController extends Controller
{
    use HandlesListing;

    protected function modelClass(): string
    {
        return Manga::class;
    }

    protected function genreClass(): string
    {
        return MangaGenre::class;
    }

    protected function cachePrefix(): string
    {
        return 'manga';
    }

    protected function listView(): string
    {
        return 'manga-list';
    }

    protected function listVariableName(): string
    {
        return 'mangaList';
    }

    protected function itemLabel(): string
    {
        return 'Manga';
    }

    public function completed()
    {
        return $this->renderList(
            $this->baseQuery()->where('status', 'Completed')->latest(),
            'Completed Manga'
        );
    }

    public function index()
    {
        return $this->newest();
    }

    protected function applySort($query, ?string $sort): void
    {
        if ($sort === 'chapters') {
            $query->orderBy('chapters_count', 'desc');
            return;
        }
        \App\Http\Controllers\Concerns\HandlesListing::applySort($query, $sort);
    }

    public function filter(Request $request)
    {
        $query = $this->baseQuery();

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
            $genreSlugs = (array) $request->genres;
            $genreIds = MangaGenre::whereIn('slug', $genreSlugs)->pluck('id');
            $query->whereHas('genres', fn($q) => $q->whereIn('manga_genre_relation.manga_genre_id', $genreIds));
        }

        $this->applySort($query, $request->sort);

        return $this->renderList(
            $query->paginate(24)->withQueryString(),
            'Filter Results'
        );
    }
}
