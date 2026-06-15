<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Anime;
use App\Models\Chapter;
use App\Models\Episode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait HandlesListing
{
    abstract protected function modelClass(): string;
    abstract protected function genreClass(): string;
    abstract protected function cachePrefix(): string;
    abstract protected function listView(): string;
    abstract protected function listVariableName(): string;
    abstract protected function itemLabel(): string;

    protected function getCachedGenres()
    {
        return Cache::remember(
            $this->cachePrefix() . '_genres_list',
            1800,
            fn () => ($this->genreClass())::select('id', 'name', 'slug')->get()
        );
    }

    protected function baseQuery(): Builder
    {
        $class = $this->modelClass();

        return $class::query()->with('genres'); // ✅ eager loading
    }

    protected function renderList(Builder $query, string $title)
    {
        $list = $query->paginate(24);
        $genres = $this->getCachedGenres();
        $varName = $this->listVariableName();

        return view($this->listView(), [
            $varName => $list,
            'title' => $title,
            'genres' => $genres
        ]);
    }

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
            $this->cachePrefix() . '_recently_updated_ids',
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
                    $query->whereBetween('year', [$decade * 10, ($decade * 10) + 9]);
                } else {
                    $query->where('year', (int) $request->year);
                }
            }

            if ($request->filled('season')) {
                $query->where('season', $request->season);
            }

            if ($request->filled('country')) {
                $query->where('country', $request->country);
            }

            if ($request->filled('rating')) {
                $query->where('rating', $request->rating);
            }

            if ($request->filled('genres')) {
                $query->whereHas('genres', function ($q) use ($request) {
                    $q->whereIn('slug', (array) $request->genres);
                });
            }

            $this->applySort($query, $request->sort);

            return $this->renderList(
                $query->withQueryString(),
                'Filter Results'
            );

        } catch (\Throwable $e) {
            Log::error('Listing filter failed', [
                'error' => $e->getMessage(),
                'params' => $request->all(),
            ]);

            return back()->with('error', 'Failed to filter results.');
        }
    }

    protected function applySort(Builder $query, ?string $sort): void
    {
        switch ($sort) {
            case 'updated':
                $query->latest('updated_at');
                break;
            case 'added':
                $query->latest('created_at');
                break;
            case 'views':
                $query->orderByDesc('views');
                break;
            case 'score':
                $query->orderByDesc('score');
                break;
            case 'rating':
                $query->orderByDesc('rating');
                break;
            case 'name':
                $query->orderBy('title');
                break;
            case 'episodes':
            case 'chapters':
                $query->orderByDesc('episodes_count');
                break;
            case 'release':
                $query->orderByDesc('year');
                break;
            default:
                $query->latest();
        }
    }
}