<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RelatedContentService
{
    public function byGenres(Model $model, Collection $genres, string $relationName, int $ttl = 600, int $limit = 8): Collection
    {
        $cacheKey = "related_{$relationName}_{$model->getKey()}";

        return Cache::remember($cacheKey, $ttl, function () use ($model, $genres, $relationName, $limit) {
            $genreIds = $genres->pluck('id')->toArray();

            if (empty($genreIds)) {
                return collect();
            }

            $modelTable = $model->getTable();

            return $model->newQuery()
                ->whereHas($relationName, function ($q) use ($genreIds) {
                    $q->whereIn('id', $genreIds);
                }, '>=', count($genreIds))
                ->where("{$modelTable}.{$model->getKeyName()}", '!=', $model->getKey())
                ->orderBy('views', 'desc')
                ->take($limit)
                ->get();
        });
    }

    public function flush(string $relationName, int $modelId): void
    {
        Cache::forget("related_{$relationName}_{$modelId}");
    }
}
