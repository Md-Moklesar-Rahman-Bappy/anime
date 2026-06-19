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

        /*
        |--------------------------------------------------------------------------
        | SAFE CACHE KEY (IMPORTANT)
        |--------------------------------------------------------------------------
        */
        $genreHash = md5(json_encode($genreIds));

        $cacheKey = sprintf(
            'related:%s:%s:%s:%s:%d',
            class_basename($model),
            $relationName,
            $model->getKey(),
            $genreHash,
            $limit
        );

        return Cache::remember($cacheKey, $ttl, function () use ($model, $genreIds, $relationName, $limit) {

            if (empty($genreIds)) {
                return $this->fallback($model, $limit);
            }

            $query = $model->newQuery()
                ->select(['id', 'title', 'slug', 'thumbnail', 'views'])
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
                ->limit($limit * 3) // slightly higher pool
                ->get();

            if ($query->isEmpty()) {
                return $this->fallback($model, $limit);
            }

            /*
            |--------------------------------------------------------------------------
            | LIGHT RANDOMIZATION
            |--------------------------------------------------------------------------
            */
            return $query
                ->shuffle()
                ->take($limit)
                ->values();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | FALLBACK STRATEGY (OPTIMIZED)
    |--------------------------------------------------------------------------
    */
    protected function fallback(Model $model, int $limit): Collection
    {
        return $model->newQuery()
            ->select(['id', 'title', 'slug', 'thumbnail', 'views'])
            ->where($model->getKeyName(), '!=', $model->getKey())
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | CACHE FLUSH (IMPROVED)
    |--------------------------------------------------------------------------
    */
    public function flush(Model $model, string $relationName): void
    {
        // ⚠️ exact cache key unknown due to genre hash
        // recommended: use tag-based cache OR short TTL

        // fallback: flush all related cache (optional)
        Cache::flush();
    }
}
