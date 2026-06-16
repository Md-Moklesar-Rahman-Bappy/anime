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
        Paginator::useTailwind();

        /*
        |--------------------------------------------------------------------------
        | Comment Rate Limiter
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('comments', function (Request $request) {
            return Limit::perMinute(30)
                ->by($request->user()?->id ?: $request->ip());
        });

        /*
        |--------------------------------------------------------------------------
        | Report Rate Limiter (stricter)
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('reports', function (Request $request) {
            return Limit::perMinute(5) // ✅ reduced for safety
                ->by($request->user()?->id ?: $request->ip());
        });

        /*
        |--------------------------------------------------------------------------
        | Favorites Rate Limiter
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('favorites', function (Request $request) {
            return Limit::perMinute(50)
                ->by($request->user()?->id ?: $request->ip());
        });

        /*
        |--------------------------------------------------------------------------
        | Streaming Rate Limiter (CRITICAL)
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('stream', function (Request $request) {
            return Limit::perMinute(30)
                ->by($request->ip())
                ->response(function () {
                    return response('Too many streaming requests', 429);
                });
        });

        /*
        |--------------------------------------------------------------------------
        | Global API Rate Limiter (IMPORTANT)
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(120)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'error' => 'Too many requests',
                    ], 429);
                });
        });
    }
}