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
    const TTL = 300;       // 5 minutes
    const SHORT_TTL = 120; // 2 minutes

    public function index()
    {
        try {
            // ✅ Main cached data
            $data = Cache::remember('admin_manga_dashboard', self::TTL, function () {

                $stats = [
                    'totalManga' => Manga::count(),
                    'totalChapters' => Chapter::count(),
                    'totalUsers' => User::count(),
                    'totalMangaViews' => Manga::sum('views'),
                ];

                $recentManga = Manga::with('genres')
                    ->latest()
                    ->take(5)
                    ->get();

                $popularManga = Manga::orderByDesc('views')
                    ->take(5)
                    ->get();

                $mangaByType = Manga::select('type', DB::raw('COUNT(*) as count'))
                    ->whereNotNull('type')
                    ->groupBy('type')
                    ->orderByDesc('count')
                    ->pluck('count', 'type');

                $mangaByStatus = Manga::select('status', DB::raw('COUNT(*) as count'))
                    ->whereNotNull('status')
                    ->groupBy('status')
                    ->orderByDesc('count')
                    ->pluck('count', 'status');

                return compact(
                    'stats',
                    'recentManga',
                    'popularManga',
                    'mangaByType',
                    'mangaByStatus'
                );
            });

            // ✅ Separate faster-changing data
            $recentChapters = Cache::remember(
                'admin_manga_recent_chapters',
                self::SHORT_TTL,
                fn() => Chapter::with('manga:id,title,slug,thumbnail')
                    ->latest()
                    ->take(5)
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
}
