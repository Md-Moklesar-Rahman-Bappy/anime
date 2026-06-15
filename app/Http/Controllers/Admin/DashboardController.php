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
        return view('admin.dashboard', [
            'stats' => $this->getStats(),
            'recentAnime' => $this->recentAnime(),
            'recentEpisodes' => $this->recentEpisodes(),
            'recentUsers' => $this->recentUsers(),
            'userGrowth' => $this->userGrowth(),
            'animeByType' => $this->animeByType(),
            'animeByStatus' => $this->animeByStatus(),
            'popularAnime' => $this->popularAnime(),
            'reportsByType' => $this->reportsByType(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Stats
    |--------------------------------------------------------------------------
    */

    protected function getStats(): array
    {
        return Cache::remember('dashboard.stats.v1', self::TTL, function () {

            $animeViews = Anime::sum('views');
            $mangaViews = Manga::sum('views');

            return [
                'totalAnime' => Anime::count(),
                'totalEpisodes' => Episode::count(),
                'totalUsers' => User::count(),
                'totalReports' => Report::where('status', 'pending')->count(),
                'totalRequests' => AnimeRequest::where('status', 'pending')->count(),
                'totalManga' => Manga::count(),
                'totalChapters' => Chapter::count(),
                'totalComments' => Comment::count(),
                'totalViews' => $animeViews + $mangaViews,
            ];
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Recent Data
    |--------------------------------------------------------------------------
    */

    protected function recentAnime()
    {
        return Cache::remember('dashboard.recent_anime', self::TTL,
            fn () => Anime::latest()->limit(5)->get()
        );
    }

    protected function recentEpisodes()
    {
        return Cache::remember('dashboard.recent_episodes', self::TTL,
            fn () => Episode::with('anime:id,title,slug')
                ->latest()
                ->limit(5)
                ->get()
        );
    }

    protected function recentUsers()
    {
        return Cache::remember('dashboard.recent_users', self::TTL,
            fn () => User::latest()->limit(5)->get()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Analytics
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
                ->map(function ($row) {
                    $row->label = date('M Y', mktime(0, 0, 0, $row->month, 1, $row->year));
                    return $row;
                });
        });
    }

    protected function animeByType()
    {
        return Cache::remember('dashboard.anime_type', self::TTL, fn () =>
            Anime::select('type', DB::raw('COUNT(*) as count'))
                ->whereNotNull('type')
                ->groupBy('type')
                ->pluck('count', 'type')
        );
    }

    protected function animeByStatus()
    {
        return Cache::remember('dashboard.anime_status', self::TTL, fn () =>
            Anime::select('status', DB::raw('COUNT(*) as count'))
                ->whereNotNull('status')
                ->groupBy('status')
                ->pluck('count', 'status')
        );
    }

    protected function popularAnime()
    {
        return Cache::remember('dashboard.popular_anime', self::TTL, fn () =>
            Anime::orderByDesc('views')->limit(5)->get()
        );
    }

    protected function reportsByType()
    {
        return Cache::remember('dashboard.reports_type', self::TTL, fn () =>
            Report::select('issue_type', DB::raw('COUNT(*) as count'))
                ->where('status', 'pending')
                ->groupBy('issue_type')
                ->pluck('count', 'issue_type')
        );
    }
}