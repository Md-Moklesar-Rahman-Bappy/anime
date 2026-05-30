<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Manga;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MangaDashboardController extends Controller
{
    public function index()
    {
        $totalManga = Manga::count();
        $totalChapters = Chapter::count();
        $totalUsers = User::count();
        $totalMangaViews = Manga::sum('views');

        $recentManga = Manga::latest()->take(5)->get();
        $recentChapters = Chapter::with('manga:id,title,slug,thumbnail')->latest()->take(5)->get();

        $popularManga = Manga::orderByDesc('views')->take(5)->get();

        $mangaByType = Manga::select('type', DB::raw('COUNT(*) as count'))
            ->whereNotNull('type')
            ->groupBy('type')
            ->orderByDesc('count')
            ->get()
            ->pluck('count', 'type');

        $mangaByStatus = Manga::select('status', DB::raw('COUNT(*) as count'))
            ->whereNotNull('status')
            ->groupBy('status')
            ->orderByDesc('count')
            ->get()
            ->pluck('count', 'status');

        return view('admin.manga-dashboard', compact(
            'totalManga', 'totalChapters', 'totalUsers', 'totalMangaViews',
            'recentManga', 'recentChapters', 'popularManga',
            'mangaByType', 'mangaByStatus'
        ));
    }
}
