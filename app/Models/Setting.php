<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::saved(fn ($setting) => static::clearCache($setting->key));
        static::deleted(fn ($setting) => static::clearCache($setting->key));
    }

    protected static function clearCache(string $key): void
    {
        Cache::forget("setting_{$key}");
    }

    /*
    |--------------------------------------------------------------------------
    | Static Helpers (VERY IMPORTANT)
    |--------------------------------------------------------------------------
    */

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            return $setting ? $setting->value : $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget("setting_{$key}");
    }

    public static function getInt(string $key, int $default = 0): int
    {
        return (int) static::get($key, $default);
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        return filter_var(static::get($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    public static function getJson(string $key, array $default = []): array
    {
        $value = static::get($key);

        return $value ? json_decode($value, true) : $default;
    }
}