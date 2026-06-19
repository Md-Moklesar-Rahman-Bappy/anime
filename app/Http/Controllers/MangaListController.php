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

    /*
    |--------------------------------------------------------------------------
    | COMPLETED
    |--------------------------------------------------------------------------
    */

    public function completed()
    {
        return $this->renderList(
            $this->baseQuery()
                ->where('status', 'Completed')
                ->latest(),
            'Completed Manga'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DEFAULT
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        return $this->newest();
    }

    /*
    |--------------------------------------------------------------------------
    | CUSTOM SORT
    |--------------------------------------------------------------------------
    */

    protected function applySort($query, ?string $sort): void
    {
        if ($sort === 'chapters') {
            $query->orderByDesc('chapters_count');
            return;
        }

        parent::applySort($query, $sort);
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER SYSTEM
    |--------------------------------------------------------------------------
    */

    public function filter(Request $request)
    {
        try {
            $query = $this->baseQuery();

            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */
            if ($request->filled('q')) {
                $safe = '%' . addcslashes($request->input('q'), '%_') . '%';
                $query->where('title', 'like', $safe);
            }

            /*
            |--------------------------------------------------------------------------
            | Type
            |--------------------------------------------------------------------------
            */
            if ($request->filled('type')) {
                $query->where('type', $request->input('type'));
            }

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */
            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            /*
            |--------------------------------------------------------------------------
            | Year / Decade
            |--------------------------------------------------------------------------
            */
            if ($request->filled('year')) {
                $year = $request->input('year');

                if (str_ends_with($year, 's')) {
                    $decade = (int) substr($year, 0, -1);
                    $query->whereBetween('year', [
                        $decade * 10,
                        $decade * 10 + 9,
                    ]);
                } else {
                    $query->where('year', (int) $year);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Genres
            |--------------------------------------------------------------------------
            */
            if ($request->filled('genres')) {
                $genreSlugs = (array) $request->input('genres');

                $query->whereHas('genres', function ($q) use ($genreSlugs) {
                    $q->whereIn('slug', $genreSlugs);
                });
            }

            /*
            |--------------------------------------------------------------------------
            | Sort
            |--------------------------------------------------------------------------
            */
            $this->applySort($query, $request->input('sort'));

            /*
            |--------------------------------------------------------------------------
            | RESPONSE
            |--------------------------------------------------------------------------
            */
            return $this->renderList(
                $query->withQueryString(),
                'Filter Results'
            );

        } catch (\Throwable $e) {

            $this->logError('Manga list filter failed', $e, [
                'params' => $request->all(),
            ]);

            return $this->redirectError('Failed to filter results.');
        }
    }
}