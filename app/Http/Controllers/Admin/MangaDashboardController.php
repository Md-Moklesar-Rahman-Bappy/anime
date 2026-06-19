<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Manga;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MangaDashboardController extends Controller
{
    protected const TTL = 300;
    protected const SHORT_TTL = 120;

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        try {
            $data = Cache::remember('manga.dashboard.main', self::TTL, function () {
                return [
                    'stats'            => $this->getStats(),
                    'recentManga'      => $this->getRecentManga(),
                    'popularManga'     => $this->getPopularManga(),
                    'mangaByType'      => $this->getMangaByType(),
                    'mangaByStatus'    => $this->getMangaByStatus(),
                ];
            });

            /*
            |--------------------------------------------------------------------------
            | RECENT CHAPTERS (SHORT CACHE)
            |--------------------------------------------------------------------------
            */
            $recentChapters = Cache::remember(
                'manga.dashboard.chapters',
                self::SHORT_TTL,
                fn() => Chapter::with('manga:id,title,slug,thumbnail')
                    ->select('id', 'manga_id', 'number', 'title', 'created_at')
                    ->latest()
                    ->limit(5)
                    ->get()
            );

            return view('admin.manga-dashboard', [
                ...$data,
                'recentChapters' => $recentChapters,
            ]);
        } catch (\Throwable $e) {

            $this->logError('Manga dashboard failed', $e);

            return $this->redirectError('Failed to load dashboard.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STATS
    |--------------------------------------------------------------------------
    */
    protected function getStats(): array
    {
        return [
            'totalManga'     => Manga::count(),
            'totalChapters'  => Chapter::count(),
            'totalUsers'     => User::count(),

            // ✅ safe numeric casting
            'totalViews'     => (int) Manga::sum('views'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RECENT MANGA
    |--------------------------------------------------------------------------
    */
    protected function getRecentManga()
    {
        return Manga::with('genres:id,name')
            ->select('id', 'title', 'slug', 'thumbnail', 'updated_at')
            ->latest()
            ->limit(5)
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | POPULAR MANGA
    |--------------------------------------------------------------------------
    */
    protected function getPopularManga()
    {
        return Manga::select('id', 'title', 'slug', 'thumbnail', 'views')
            ->orderByDesc('views')
            ->limit(5)
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | MANGA BY TYPE
    |--------------------------------------------------------------------------
    */
    protected function getMangaByType(): array
    {
        return Manga::whereNotNull('type')
            ->select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | MANGA BY STATUS
    |--------------------------------------------------------------------------
    */
    protected function getMangaByStatus(): array
    {
        return Manga::whereNotNull('status')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }
}
