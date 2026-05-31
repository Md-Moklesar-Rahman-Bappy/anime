<?php

namespace App\Http\Controllers\Concerns;

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

    protected function getCachedGenres()
    {
        return Cache::remember($this->cachePrefix() . '_genres_list', 1800, fn() => ($this->genreClass())::all());
    }

    protected function baseQuery(): Builder
    {
        $class = $this->modelClass();

        return $class::query();
    }

    protected function renderList(Builder $query, string $title)
    {
        $list = $query->paginate(24);
        $genres = $this->getCachedGenres();
        $varName = $this->listVariableName();

        return view($this->listView(), [$varName => $list, 'title' => $title, 'genres' => $genres]);
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
        $recentIds = Cache::remember($this->cachePrefix() . '_recently_updated_ids', 300, function () {
            $class = $this->modelClass();
            $foreignKey = $class === \App\Models\Anime::class ? 'anime_id' : 'manga_id';
            $recentModel = $class === \App\Models\Anime::class ? \App\Models\Episode::class : \App\Models\Chapter::class;

            return $recentModel::where('created_at', '>=', now()->subWeek())
                ->distinct()
                ->pluck($foreignKey);
        });

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
            $this->baseQuery()->orderBy('views', 'desc'),
            "Trending {$this->itemLabel()}"
        );
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
                $q->whereIn('slug', $genreSlugs);
            });
        }

        $this->applySort($query, $request->sort);

        return $this->renderList(
            $query->paginate(24)->withQueryString(),
            'Filter Results'
        );
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
            case 'chapters':
                $query->orderBy('episodes_count', 'desc');
                break;
            case 'release':
                $query->orderBy('year', 'desc');
                break;
            default:
                $query->latest();
                break;
        }
    }
}
