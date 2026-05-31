<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Manga;
use Illuminate\Support\Facades\Cache;

class MangaHomeController extends Controller
{
    public function index()
    {
        $featured = Cache::remember('manga_home_featured', 600, function () {
            return Manga::where('featured', true)->orderBy('featured_order')->take(5)->get();
        });

        $trending = Cache::remember('manga_home_trending', 600, function () {
            return Manga::orderBy('views', 'desc')->take(12)->get();
        });

        $recentChapters = Cache::remember('manga_home_recent_chapters', 300, function () {
            return Chapter::with(['manga'])
                ->latest()
                ->take(24)
                ->get();
        });

        $newManga = Cache::remember('manga_home_new', 300, function () {
            return Manga::latest()->take(20)->get();
        });

        $mostViewed = Cache::remember('manga_home_most_viewed', 600, function () {
            return Manga::orderBy('views', 'desc')->take(10)->get();
        });

        return view('manga-home', compact('featured', 'trending', 'recentChapters', 'newManga', 'mostViewed'));
    }
}
