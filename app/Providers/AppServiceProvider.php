<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configurePagination();
        $this->configureUrls();
        $this->configureRateLimiters();
    }

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    | Use Tailwind pagination + your custom views.
    | Edit views in: resources/views/vendor/pagination/
    */
    protected function configurePagination(): void
    {
        Paginator::useTailwind();

        // Use our custom Tailwind views
        Paginator::defaultView('vendor.pagination.default');
        Paginator::defaultSimpleView('vendor.pagination.simple-default');
    }

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    | Force HTTPS in production for security (mixed content prevention).
    */
    protected function configureUrls(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Rate Limiters
    |--------------------------------------------------------------------------
    */
    protected function configureRateLimiters(): void
    {
        // ─── Comments (medium traffic) ───
        RateLimiter::for('comments', function (Request $request) {
            return Limit::perMinute(30)
                ->by($this->userOrIp($request))
                ->response(fn() => $this->limitResponse(
                    'You are commenting too fast. Please wait a moment.'
                ));
        });

        // ─── Reports (strict — abuse prevention) ───
        RateLimiter::for('reports', function (Request $request) {
            return Limit::perMinute(5)
                ->by($this->userOrIp($request))
                ->response(fn() => $this->limitResponse(
                    'You can only submit a few reports per minute.'
                ));
        });

        // ─── Favorites / Watchlist (UI clicks) ───
        RateLimiter::for('favorites', function (Request $request) {
            return Limit::perMinute(50)
                ->by($this->userOrIp($request));
        });

        // ─── Streaming (CRITICAL — bandwidth protection) ───
        RateLimiter::for('stream', function (Request $request) {
            return Limit::perMinute(30)
                ->by($request->ip())
                ->response(fn() => response(
                    'Too many streaming requests. Please slow down.',
                    429
                ));
        });

        // ─── Search (anti-scraping) ───
        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(60)
                ->by($this->userOrIp($request))
                ->response(fn() => $this->limitResponse(
                    'Too many searches. Please slow down.'
                ));
        });

        // ─── Authentication (anti-brute-force) ───
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');

            return [
                // 5 attempts per minute per email
                Limit::perMinute(5)->by(strtolower($email) . '|' . $request->ip()),
                // 20 attempts per hour per IP (catches distributed attacks)
                Limit::perHour(20)->by($request->ip()),
            ];
        });

        // ─── Password Reset (prevent email spam) ───
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        // ─── Registration (anti-spam) ───
        RateLimiter::for('register', function (Request $request) {
            return Limit::perHour(5)->by($request->ip());
        });

        // ─── Contact Form (anti-spam) ───
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perHour(3)->by($this->userOrIp($request))
                ->response(fn() => $this->limitResponse(
                    'You have submitted too many contact messages. Please try again later.'
                ));
        });

        // ─── Global API ───
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($this->userOrIp($request))
                ->response(fn() => response()->json([
                    'error'   => 'Too many requests',
                    'message' => 'Please slow down and try again shortly.',
                ], 429));
        });

        // ─── Global (catch-all) ───
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(120)
                ->by($this->userOrIp($request))
                ->response(fn() => response()->json([
                    'error' => 'Too many requests',
                ], 429));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Build a rate-limit key based on user ID or IP.
     */
    protected function userOrIp(Request $request): string
    {
        return (string) ($request->user()?->id ?: $request->ip());
    }

    /**
     * Standard 429 response for limited routes.
     */
    protected function limitResponse(string $message)
    {
        return response()->json([
            'error'   => 'rate_limited',
            'message' => $message,
        ], 429);
    }
}
