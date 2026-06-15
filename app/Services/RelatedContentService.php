<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RelatedContentService
{
    public function byGenres(
        Model $model,
        Collection $genres,
        string $relationName,
        int $ttl = 600,
        int $limit = 8
    ): Collection {

        $genreIds = $genres->pluck('id')->sort()->values()->toArray();

        $cacheKey = sprintf(
            'related_%s_%s_%s_limit_%d',
            class_basename($model),
            $relationName,
            $model->getKey(),
            $limit
        );

        return Cache::remember($cacheKey, $ttl, function () use ($model, $genreIds, $relationName, $limit) {

            if (empty($genreIds)) {
                return $this->fallback($model, $limit);
            }

            $query = $model->newQuery()
                ->whereHas($relationName, function ($q) use ($genreIds) {
                    $q->whereIn('id', $genreIds);
                })
                ->where($model->getKeyName(), '!=', $model->getKey())
                ->withCount([
                    $relationName . ' as matching_genres_count' => function ($q) use ($genreIds) {
                        $q->whereIn('id', $genreIds);
                    }
                ])
                ->orderByDesc('matching_genres_count')
                ->orderByDesc('views')
                ->limit($limit * 2)
                ->get();

            if ($query->isEmpty()) {
                return $this->fallback($model, $limit);
            }

            return $query
                ->shuffle()
                ->take($limit)
                ->values();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Fallback Strategy (VERY IMPORTANT)
    |--------------------------------------------------------------------------
    */

    protected function fallback(Model $model, int $limit): Collection
    {
        return $model->newQuery()
            ->where($model->getKeyName(), '!=', $model->getKey())
            ->orderByDesc('views')
            ->inRandomOrder()
            ->take($limit)
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Cache Flush
    |--------------------------------------------------------------------------
    */

    public function flush(string $relationName, int $modelId): void
    {
        $prefix = "related_{$relationName}_{$modelId}";

        Cache::forget($prefix);
    }
}