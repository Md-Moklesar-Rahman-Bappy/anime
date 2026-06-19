<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */
        Paginator::useBootstrapFive();

        /*
        |--------------------------------------------------------------------------
        | Comment Rate Limiter
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('comments', function (Request $request) {
            return Limit::perMinute(30)
                ->by($this->userOrIp($request));
        });

        /*
        |--------------------------------------------------------------------------
        | Report Rate Limiter (Stricter)
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('reports', function (Request $request) {
            return Limit::perMinute(5)
                ->by($this->userOrIp($request));
        });

        /*
        |--------------------------------------------------------------------------
        | Favorites Rate Limiter
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('favorites', function (Request $request) {
            return Limit::perMinute(50)
                ->by($this->userOrIp($request));
        });

        /*
        |--------------------------------------------------------------------------
        | Streaming Rate Limiter (CRITICAL)
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('stream', function (Request $request) {
            return Limit::perMinute(30)
                ->by($request->ip())
                ->response(fn () => response('Too many streaming requests', 429));
        });

        /*
        |--------------------------------------------------------------------------
        | Global API Rate Limiter
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(120)
                ->by($this->userOrIp($request))
                ->response(fn () => response()->json([
                    'error' => 'Too many requests',
                ], 429));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: User or IP Key
    |--------------------------------------------------------------------------
    */
    protected function userOrIp(Request $request): string
    {
        return (string) ($request->user()?->id ?: $request->ip());
    }
}
