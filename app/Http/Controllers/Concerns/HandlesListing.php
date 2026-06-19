<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Anime;
use App\Models\Chapter;
use App\Models\Episode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

trait HandlesListing
{
    abstract protected function modelClass(): string;
    abstract protected function genreClass(): string;
    abstract protected function cachePrefix(): string;
    abstract protected function listView(): string;
    abstract protected function listVariableName(): string;
    abstract protected function itemLabel(): string;

    /*
    |--------------------------------------------------------------------------
    | GENRES CACHE
    |--------------------------------------------------------------------------
    */
    protected function getCachedGenres()
    {
        return Cache::remember(
            $this->cachePrefix() . '_genres_list',
            1800,
            fn () => ($this->genreClass())::select('id', 'name', 'slug')->get()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BASE QUERY
    |--------------------------------------------------------------------------
    */
    protected function baseQuery(): Builder
    {
        $class = $this->modelClass();

        return $class::query()
            ->select('id', 'title', 'slug', 'thumbnail', 'status', 'type', 'year', 'views', 'rating', 'created_at', 'updated_at')
            ->with('genres:id,name,slug');
    }

    /*
    |--------------------------------------------------------------------------
    | RENDER LIST
    |--------------------------------------------------------------------------
    */
    protected function renderList(Builder $query, string $title)
    {
        $list = $query->paginate(24)->withQueryString();
        $genres = $this->getCachedGenres();
        $varName = $this->listVariableName();

        return view($this->listView(), [
            $varName => $list,
            'title' => $title,
            'genres' => $genres,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PRESETS
    |--------------------------------------------------------------------------
    */
    public function newest()
    {
        return $this->renderList(
            $this->baseQuery()->latest(),
            "Newest {$this->itemLabel()}"
        );
    }

    public function updated()
    {
        $recentIds = Cache::remember(
            $this->cachePrefix() . '_recent_ids',
            300,
            function () {
                $class = $this->modelClass();

                $foreignKey = $class === Anime::class ? 'anime_id' : 'manga_id';
                $recentModel = $class === Anime::class ? Episode::class : Chapter::class;

                return $recentModel::where('created_at', '>=', now()->subWeek())
                    ->distinct()
                    ->pluck($foreignKey);
            }
        );

        return $this->renderList(
            $this->baseQuery()->whereIn('id', $recentIds)->latest(),
            'Recently Updated'
        );
    }

    public function ongoing()
    {
        return $this->renderList(
            $this->baseQuery()->where('status', 'Ongoing')->latest(),
            "Ongoing {$this->itemLabel()}"
        );
    }

    public function trending()
    {
        return $this->renderList(
            $this->baseQuery()->orderByDesc('views'),
            "Trending {$this->itemLabel()}"
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER
    |--------------------------------------------------------------------------
    */
    public function filter(Request $request)
    {
        try {
            $query = $this->baseQuery();

            $search = trim((string) $request->input('q'));

            if ($search !== '') {
                $safe = '%' . addcslashes($search, '%_') . '%';
                $query->where('title', 'like', $safe);
            }

            if ($request->filled('type')) {
                $query->where('type', $request->input('type'));
            }

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->filled('year')) {
                $year = $request->input('year');

                if (str_ends_with($year, 's')) {
                    $decade = (int) substr($year, 0, -1);
                    $query->whereBetween('year', [$decade * 10, ($decade * 10) + 9]);
                } else {
                    $query->where('year', (int) $year);
                }
            }

            if ($request->filled('season')) {
                $query->where('season', $request->input('season'));
            }

            if ($request->filled('country')) {
                $query->where('country', $request->input('country'));
            }

            if ($request->filled('rating')) {
                $query->where('rating', $request->input('rating'));
            }

            if ($request->filled('genres')) {
                $query->whereHas('genres', function ($q) use ($request) {
                    $q->whereIn('slug', (array) $request->input('genres'));
                });
            }

            $this->applySort($query, $request->input('sort'));

            return $this->renderList($query, 'Filter Results');

        } catch (\Throwable $e) {

            $this->logError('Listing filter failed', $e, [
                'params' => $request->all(),
            ]);

            return back()->with('error', 'Failed to filter results.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SORT
    |--------------------------------------------------------------------------
    */
    protected function applySort(Builder $query, ?string $sort): void
    {
        match ($sort) {
            'updated' => $query->latest('updated_at'),
            'added' => $query->latest('created_at'),
            'views' => $query->orderByDesc('views'),
            'score' => $query->orderByDesc('score'),
            'rating' => $query->orderByDesc('rating'),
            'name' => $query->orderBy('title'),
            'episodes', 'chapters' => $query->orderByDesc('episodes_count'),
            'release' => $query->orderByDesc('year'),
            default => $query->latest(),
        };
    }
}