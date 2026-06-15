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
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('dashboard.stats', 3600, function () {
            return [
                'totalAnime' => Anime::count(),
                'totalEpisodes' => Episode::count(),
                'totalUsers' => User::count(),
                'totalReports' => Report::where('status', 'pending')->count(),
                'totalRequests' => AnimeRequest::where('status', 'pending')->count(),
                'totalManga' => Manga::count(),
                'totalChapters' => Chapter::count(),
                'totalComments' => Comment::count(),
                'totalViews' => Anime::sum('views') + Manga::sum('views'),
                'animeByType' => Anime::select('type', DB::raw('COUNT(*) as count'))
                    ->whereNotNull('type')
                    ->groupBy('type')
                    ->orderByDesc('count')
                    ->get()
                    ->pluck('count', 'type'),
                'animeByStatus' => Anime::select('status', DB::raw('COUNT(*) as count'))
                    ->whereNotNull('status')
                    ->groupBy('status')
                    ->orderByDesc('count')
                    ->get()
                    ->pluck('count', 'status'),
                'popularAnime' => Anime::orderByDesc('views')->take(5)->get(),
                'userGrowth' => User::select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('COUNT(*) as count')
                )
                    ->where('created_at', '>=', now()->subMonths(12))
                    ->groupBy('year', 'month')
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get()
                    ->map(function ($item) {
                        $item->label = Carbon::create($item->year, $item->month)->format('M Y');

                        return $item;
                    }),
                'reportsByType' => Report::select('issue_type', DB::raw('COUNT(*) as count'))
                    ->where('status', 'pending')
                    ->groupBy('issue_type')
                    ->orderByDesc('count')
                    ->get()
                    ->pluck('count', 'issue_type'),
            ];
        });

        $recentAnime = Cache::remember('dashboard.recent.anime', 600, function () {
            return Anime::latest()->take(5)->get();
        });

        $recentEpisodes = Cache::remember('dashboard.recent.episodes', 600, function () {
            return Episode::with('anime:id,title,slug')->latest()->take(5)->get();
        });

        $recentUsers = Cache::remember('dashboard.recent.users', 600, function () {
            return User::latest()->take(5)->get();
        });

        return view('admin.dashboard', compact(
            'recentAnime', 'recentEpisodes', 'recentUsers',
            'stats'
        ) + $stats);
    }
}
