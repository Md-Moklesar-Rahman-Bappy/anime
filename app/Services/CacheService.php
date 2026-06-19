<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    public const DEFAULT_TTL = 300;
    public const LONG_TTL = 1800;

    /*
    |--------------------------------------------------------------------------
    | BASIC CACHE
    |--------------------------------------------------------------------------
    */
    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        try {
            return Cache::remember($key, $ttl, $callback);
        } catch (\Throwable $e) {

            logger()->error('Cache remember failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return $callback(); // ✅ fallback
        }
    }

    public function forget(string $key): void
    {
        try {
            Cache::forget($key);
        } catch (\Throwable $e) {
            logger()->warning('Cache forget failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GROUP CACHE (SAFE)
    |--------------------------------------------------------------------------
    */
    public function rememberGroup(string $group, string $key, int $ttl, callable $callback): mixed
    {
        try {
            if ($this->supportsTags()) {
                return Cache::tags($group)->remember($key, $ttl, $callback);
            }

            // fallback to normal cache
            return $this->remember("{$group}:{$key}", $ttl, $callback);
        } catch (\Throwable $e) {

            logger()->error('Cache group remember failed', [
                'group' => $group,
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return $callback();
        }
    }

    public function flushGroup(string $group): void
    {
        try {
            if ($this->supportsTags()) {
                Cache::tags($group)->flush();
                return;
            }

            // fallback: cannot flush grouped keys safely
        } catch (\Throwable $e) {
            logger()->warning('Cache group flush failed', [
                'group' => $group,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GENRES CACHE
    |--------------------------------------------------------------------------
    */
    public function getGenres(string $modelClass): mixed
    {
        $key = "genres:" . class_basename($modelClass);

        return $this->remember($key, self::LONG_TTL, function () use ($modelClass) {
            return $modelClass::select('id', 'name', 'slug')->get();
        });
    }

    public function flushGenreCache(string $modelClass): void
    {
        $this->forget("genres:" . class_basename($modelClass));
    }

    /*
    |--------------------------------------------------------------------------
    | INTERNAL HELPERS
    |--------------------------------------------------------------------------
    */
    protected function supportsTags(): bool
    {
        try {
            return method_exists(Cache::getStore(), 'tags');
        } catch (\Throwable) {
            return false;
        }
    }
}
