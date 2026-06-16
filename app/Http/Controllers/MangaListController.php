<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesListing;
use App\Models\Manga;
use App\Models\MangaGenre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            $this->baseQuery()
                ->where('status', 'Completed')
                ->latest(),
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
            $query->orderByDesc('chapters_count');
            return;
        }

        parent::applySort($query, $sort);
    }

    public function filter(Request $request)
    {
        try {
            $query = $this->baseQuery();

            if ($request->filled('q')) {
                $safe = '%' . addcslashes($request->q, '%_') . '%';
                $query->where('title', 'like', $safe);
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('year')) {
                if (str_ends_with($request->year, 's')) {
                    $decade = (int) substr($request->year, 0, -1);
                    $query->whereBetween('year', [$decade * 10, $decade * 10 + 9]);
                } else {
                    $query->where('year', (int) $request->year);
                }
            }

            if ($request->filled('genres')) {
                $genreSlugs = (array) $request->genres;

                $query->whereHas('genres', function ($q) use ($genreSlugs) {
                    $q->whereIn('slug', $genreSlugs);
                });
            }

            $this->applySort($query, $request->sort);

            return $this->renderList(
                $query->withQueryString(),
                'Filter Results'
            );

        } catch (\Throwable $e) {
            Log::error('Manga list filter failed', [
                'params' => $request->all(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to filter results.');
        }
    }
}