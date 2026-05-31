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
    const CACHE_TTL = 300;

    public function index()
    {
        $stats = Cache::remember('admin_dashboard_stats', self::CACHE_TTL, fn() => [
            'totalAnime' => Anime::count(),
            'totalEpisodes' => Episode::count(),
            'totalUsers' => User::count(),
            'totalReports' => Report::where('status', 'pending')->count(),
            'totalRequests' => AnimeRequest::where('status', 'pending')->count(),
            'totalManga' => Manga::count(),
            'totalChapters' => Chapter::count(),
            'totalComments' => Comment::count(),
            'totalViews' => Anime::sum('views') + Manga::sum('views'),
        ]);

        $recentAnime = Cache::remember('admin_dashboard_recent_anime', self::CACHE_TTL, fn() =>
            Anime::latest()->take(5)->get()
        );

        $recentEpisodes = Cache::remember('admin_dashboard_recent_episodes', self::CACHE_TTL / 2, fn() =>
            Episode::with('anime:id,title,slug')->latest()->take(5)->get()
        );

        $recentUsers = Cache::remember('admin_dashboard_recent_users', self::CACHE_TTL, fn() =>
            User::latest()->take(5)->get()
        );

        $userGrowth = Cache::remember('admin_dashboard_user_growth', self::CACHE_TTL, fn() =>
            User::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
                ->where('created_at', '>=', now()->subMonths(12))
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->map(fn($item) => tap($item, fn($i) => $i->label = date('M Y', mktime(0, 0, 0, $i->month, 1, $i->year))))
        );

        $animeByType = Cache::remember('admin_dashboard_anime_by_type', self::CACHE_TTL, fn() =>
            Anime::select('type', DB::raw('COUNT(*) as count'))
                ->whereNotNull('type')
                ->groupBy('type')
                ->orderByDesc('count')
                ->get()
                ->pluck('count', 'type')
        );

        $animeByStatus = Cache::remember('admin_dashboard_anime_by_status', self::CACHE_TTL, fn() =>
            Anime::select('status', DB::raw('COUNT(*) as count'))
                ->whereNotNull('status')
                ->groupBy('status')
                ->orderByDesc('count')
                ->get()
                ->pluck('count', 'status')
        );

        $popularAnime = Cache::remember('admin_dashboard_popular_anime', self::CACHE_TTL, fn() =>
            Anime::orderByDesc('views')->take(5)->get()
        );

        $reportsByType = Cache::remember('admin_dashboard_reports_by_type', self::CACHE_TTL, fn() =>
            Report::select('issue_type', DB::raw('COUNT(*) as count'))
                ->where('status', 'pending')
                ->groupBy('issue_type')
                ->orderByDesc('count')
                ->get()
                ->pluck('count', 'issue_type')
        );

        return view('admin.dashboard', $stats + compact(
            'recentAnime', 'recentEpisodes', 'recentUsers',
            'userGrowth', 'animeByType', 'animeByStatus',
            'popularAnime', 'reportsByType'
        ));
    }
}
