<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ViewCounterService
{
    protected const SESSION_PREFIX = 'viewed_';
    protected const CACHE_PREFIX = 'view_';
    protected const BUFFER_PREFIX = 'views_buffer_';

    protected const CACHE_TTL = 600; // 10 minutes

    /*
    |--------------------------------------------------------------------------
    | INCREMENT VIEW (SAFE + ROBUST)
    |--------------------------------------------------------------------------
    */
    public function increment(Model $model, ?string $type = null): void
    {
        $sessionKey = $this->sessionKey($model, $type);
        $cacheKey = $this->cacheKey($model);

        /*
        |--------------------------------------------------------------------------
        | Session-level protection (per browser session)
        |--------------------------------------------------------------------------
        */
        if (session()->has($sessionKey)) {
            return;
        }

        session()->put($sessionKey, true);

        /*
        |--------------------------------------------------------------------------
        | Cache-level protection (per IP/User)
        |--------------------------------------------------------------------------
        */
        if (!Cache::add($cacheKey, true, self::CACHE_TTL)) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Increment (atomic DB operation)
        |--------------------------------------------------------------------------
        */
        $model->increment('views');
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK VIEWED
    |--------------------------------------------------------------------------
    */
    public function hasViewed(Model $model, ?string $type = null): bool
    {
        return session()->has($this->sessionKey($model, $type));
    }

    /*
    |--------------------------------------------------------------------------
    | HIGH TRAFFIC MODE (BUFFERED)
    |--------------------------------------------------------------------------
    */
    public function incrementBuffered(Model $model): void
    {
        $key = $this->bufferKey($model);

        /*
        |--------------------------------------------------------------------------
        | Atomic increment (safe in Redis)
        |--------------------------------------------------------------------------
        */
        Cache::increment($key);
    }

    public function flushBuffered(Model $model): void
    {
        $key = $this->bufferKey($model);

        $count = Cache::pull($key, 0);

        if ($count > 0) {
            $model->increment('views', (int) $count);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | KEY BUILDERS
    |--------------------------------------------------------------------------
    */

    protected function sessionKey(Model $model, ?string $type = null): string
    {
        $class = strtolower($type ?? class_basename($model));

        return self::SESSION_PREFIX . $class . '_' . $model->getKey();
    }

    protected function cacheKey(Model $model): string
    {
        $identifier = request()->user()?->id ?: request()->ip();

        return self::CACHE_PREFIX
            . strtolower(class_basename($model)) . '_'
            . $model->getKey() . '_'
            . md5((string) $identifier);
    }

    protected function bufferKey(Model $model): string
    {
        return self::BUFFER_PREFIX
            . strtolower(class_basename($model)) . '_'
            . $model->getKey();
    }
}
