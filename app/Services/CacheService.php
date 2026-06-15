<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    const DEFAULT_TTL = 300;

    const LONG_TTL = 1800;

    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        return Cache::remember($key, $ttl, $callback);
    }

    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    public function forgetByPattern(string $prefix): void
    {
        Cache::forget($prefix);
    }

    public function getGenres(string $modelClass): mixed
    {
        $cacheKey = class_basename($modelClass).'_genres_list';

        return Cache::remember($cacheKey, self::LONG_TTL, fn () => $modelClass::all());
    }

    public function flushGenreCache(string $modelClass): void
    {
        Cache::forget(class_basename($modelClass).'_genres_list');
    }

    public function getSetting(string $key): ?string
    {
        return Cache::remember("setting_{$key}", self::LONG_TTL, function () use ($key) {
            return Setting::where('key', $key)->value('value');
        });
    }

    public function flushSetting(string $key): void
    {
        Cache::forget("setting_{$key}");
    }

    public function flushSettings(): void
    {
        foreach (['setting_logo', 'setting_favicon', 'sitemap_urls'] as $key) {
            Cache::forget($key);
        }
    }
}
