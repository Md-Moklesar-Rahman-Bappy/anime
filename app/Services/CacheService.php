<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    public const DEFAULT_TTL = 300;
    public const LONG_TTL = 1800;

    /*
    |--------------------------------------------------------------------------
    | Basic Cache Wrappers
    |--------------------------------------------------------------------------
    */

    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        try {
            return Cache::remember($key, $ttl, $callback);
        } catch (\Throwable $e) {
            Log::error('Cache remember failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return $callback(); // ✅ fallback
        }
    }

    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    /*
    |--------------------------------------------------------------------------
    | Grouped Cache (Better structure)
    |--------------------------------------------------------------------------
    */

    public function rememberGroup(string $group, string $key, int $ttl, callable $callback): mixed
    {
        return Cache::tags($group)->remember($key, $ttl, $callback);
    }

    public function flushGroup(string $group): void
    {
        Cache::tags($group)->flush();
    }

    /*
    |--------------------------------------------------------------------------
    | Genres
    |--------------------------------------------------------------------------
    */

    public function getGenres(string $modelClass): mixed
    {
        $cacheKey = class_basename($modelClass) . '_genres_list';

        return $this->remember($cacheKey, self::LONG_TTL, function () use ($modelClass) {
            return $modelClass::all();
        });
    }

    public function flushGenreCache(string $modelClass): void
    {
        $this->forget(class_basename($modelClass) . '_genres_list');
    }
}