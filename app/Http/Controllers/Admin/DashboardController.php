<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\AnimeRequest;
use App\Models\Chapter;
use App\Models\Comment;
use App\Models\Episode;
use App\Models\Manga;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected const TTL = 300;

    public function index()
    {
        try {
            return view('admin.dashboard', [
                'stats'          => $this->getStats(),
                'recentAnime'    => $this->recentAnime(),
                'recentEpisodes' => $this->recentEpisodes(),
                'recentUsers'    => $this->recentUsers(),
                'userGrowth'     => $this->userGrowth(),
                'animeByType'    => $this->animeByType(),
                'animeByStatus'  => $this->animeByStatus(),
                'popularAnime'   => $this->popularAnime(),
                'reportsByType'  => $this->reportsByType(),
            ]);
        } catch (\Throwable $e) {

            $this->logError('Dashboard load failed', $e);

            return $this->redirectError('Failed to load dashboard.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CORE STATS
    |--------------------------------------------------------------------------
    */

    protected function getStats(): array
    {
        return Cache::remember('dashboard.stats', self::TTL, function () {

            return [
                'totalAnime'     => Anime::count(),
                'totalEpisodes'  => Episode::count(),
                'totalUsers'     => User::count(),
                'totalReports'   => Report::where('status', 'pending')->count(),
                'totalRequests'  => AnimeRequest::where('status', 'pending')->count(),
                'totalManga'     => Manga::count(),
                'totalChapters'  => Chapter::count(),
                'totalComments'  => Comment::count(),

                // ✅ safer aggregation (single query each)
                'totalViews' =>
                (int) Anime::sum('views') +
                    (int) Manga::sum('views'),
            ];
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RECENT DATA
    |--------------------------------------------------------------------------
    */

    protected function recentAnime()
    {
        return Cache::remember('dashboard.recent_anime', self::TTL, function () {
            return Anime::select('id', 'title', 'slug', 'updated_at')
                ->latest()
                ->limit(5)
                ->get();
        });
    }

    protected function recentEpisodes()
    {
        return Cache::remember('dashboard.recent_episodes', self::TTL, function () {
            return Episode::with('anime:id,title,slug')
                ->select('id', 'anime_id', 'number', 'title', 'created_at')
                ->latest()
                ->limit(5)
                ->get();
        });
    }

    protected function recentUsers()
    {
        return Cache::remember('dashboard.recent_users', self::TTL, function () {
            return User::select('id', 'name', 'email', 'created_at')
                ->latest()
                ->limit(5)
                ->get();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | USER GROWTH (CHART)
    |--------------------------------------------------------------------------
    */

    protected function userGrowth()
    {
        return Cache::remember('dashboard.user_growth', self::TTL, function () {

            return User::selectRaw("
                    YEAR(created_at) as year,
                    MONTH(created_at) as month,
                    COUNT(*) as count
                ")
                ->where('created_at', '>=', now()->subMonths(12))
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->map(fn($row) => [
                    'label' => date('M Y', mktime(0, 0, 0, $row->month, 1, $row->year)),
                    'count' => $row->count,
                ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ANIME DISTRIBUTION
    |--------------------------------------------------------------------------
    */

    protected function animeByType()
    {
        return Cache::remember('dashboard.anime_type', self::TTL, function () {
            return Anime::whereNotNull('type')
                ->select('type', DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray();
        });
    }

    protected function animeByStatus()
    {
        return Cache::remember('dashboard.anime_status', self::TTL, function () {
            return Anime::whereNotNull('status')
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | POPULAR CONTENT
    |--------------------------------------------------------------------------
    */

    protected function popularAnime()
    {
        return Cache::remember('dashboard.popular_anime', self::TTL, function () {
            return Anime::select('id', 'title', 'slug', 'views')
                ->orderByDesc('views')
                ->limit(5)
                ->get();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | REPORT ANALYTICS
    |--------------------------------------------------------------------------
    */

    protected function reportsByType()
    {
        return Cache::remember('dashboard.reports_type', self::TTL, function () {
            return Report::where('status', 'pending')
                ->select('issue_type', DB::raw('COUNT(*) as count'))
                ->groupBy('issue_type')
                ->pluck('count', 'issue_type')
                ->toArray();
        });
    }
}
