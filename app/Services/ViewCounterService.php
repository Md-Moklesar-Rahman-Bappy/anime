<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ViewCounterService
{
    protected const SESSION_PREFIX = 'viewed_';
    protected const CACHE_PREFIX = 'view_';
    protected const CACHE_TTL = 600; // 10 mins

    /*
    |--------------------------------------------------------------------------
    | Increment View
    |--------------------------------------------------------------------------
    */

    public function increment(Model $model, ?string $type = null): void
    {
        $sessionKey = $this->sessionKey($model, $type);
        $cacheKey = $this->cacheKey($model);

        // ✅ session-level protection
        if (session()->has($sessionKey)) {
            return;
        }

        session()->put($sessionKey, true);

        // ✅ cache-level protection (cross request / device safer)
        if (Cache::has($cacheKey)) {
            return;
        }

        Cache::put($cacheKey, true, self::CACHE_TTL);

        // ✅ increment DB safely
        $model->increment('views');
    }

    /*
    |--------------------------------------------------------------------------
    | Check Viewed
    |--------------------------------------------------------------------------
    */

    public function hasViewed(Model $model, ?string $type = null): bool
    {
        return session()->has($this->sessionKey($model, $type));
    }

    /*
    |--------------------------------------------------------------------------
    | Cache-only increment (high traffic mode)
    |--------------------------------------------------------------------------
    */

    public function incrementBuffered(Model $model): void
    {
        $key = "views_buffer_" . class_basename($model) . "_" . $model->getKey();
        Cache::increment($key);
    }

    public function flushBuffered(Model $model): void
    {
        $key = "views_buffer_" . class_basename($model) . "_" . $model->getKey();

        $count = Cache::pull($key, 0);

        if ($count > 0) {
            $model->increment('views', $count);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Key Builders
    |--------------------------------------------------------------------------
    */

    protected function sessionKey(Model $model, ?string $type = null): string
    {
        $class = $type ?? class_basename($model);

        return self::SESSION_PREFIX . strtolower($class) . "_{$model->getKey()}";
    }

    protected function cacheKey(Model $model): string
    {
        $identifier =
            request()->user()?->id
            ?? request()->ip();

        return self::CACHE_PREFIX
            . class_basename($model) . "_"
            . $model->getKey() . "_"
            . md5($identifier);
    }
}