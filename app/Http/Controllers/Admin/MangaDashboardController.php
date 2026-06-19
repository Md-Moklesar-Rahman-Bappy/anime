<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Manga;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MangaDashboardController extends Controller
{
    protected const TTL = 300;       // 5 minutes
    protected const SHORT_TTL = 120; // 2 minutes

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        try {
            $data = Cache::remember('manga.dashboard.main', self::TTL, function () {

                return [
                    'stats' => $this->getStats(),
                    'recentManga' => $this->getRecentManga(),
                    'popularManga' => $this->getPopularManga(),
                    'mangaByType' => $this->getMangaByType(),
                    'mangaByStatus' => $this->getMangaByStatus(),
                ];
            });

            $recentChapters = Cache::remember(
                'manga.dashboard.chapters',
                self::SHORT_TTL,
                fn() => Chapter::with('manga:id,title,slug,thumbnail')
                    ->latest()
                    ->limit(5)
                    ->get()
            );

            return view('admin.manga-dashboard', [
                ...$data,
                'recentChapters' => $recentChapters,
            ]);
        } catch (\Throwable $e) {
            Log::error('Manga dashboard failed', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to load dashboard.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Stats
    |--------------------------------------------------------------------------
    */
    protected function getStats(): array
    {
        return [
            'totalManga' => Manga::count(),
            'totalChapters' => Chapter::count(),
            'totalUsers' => User::count(),
            'totalViews' => Manga::sum('views') ?? 0,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Recent Manga
    |--------------------------------------------------------------------------
    */
    protected function getRecentManga()
    {
        return Manga::with('genres:id,name')
            ->latest()
            ->limit(5)
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Popular Manga
    |--------------------------------------------------------------------------
    */
    protected function getPopularManga()
    {
        return Manga::orderByDesc('views')
            ->limit(5)
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Manga by Type (Chart Ready)
    |--------------------------------------------------------------------------
    */
    protected function getMangaByType(): array
    {
        return Manga::select('type', DB::raw('COUNT(*) as count'))
            ->whereNotNull('type')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | Manga by Status (Chart Ready)
    |--------------------------------------------------------------------------
    */
    protected function getMangaByStatus(): array
    {
        return Manga::select('status', DB::raw('COUNT(*) as count'))
            ->whereNotNull('status')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }
}
