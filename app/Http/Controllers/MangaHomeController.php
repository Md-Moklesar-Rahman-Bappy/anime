<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Manga;
use Illuminate\Support\Facades\Cache;

class MangaHomeController extends Controller
{
    const CACHE_TTL = 300;

    public function index()
    {
        $featured = Cache::remember('manga_home_featured', self::CACHE_TTL, fn () => Manga::where('featured', true)->orderBy('featured_order')->take(5)->get()
        );

        $trending = Cache::remember('manga_home_trending', self::CACHE_TTL, fn () => Manga::orderBy('views', 'desc')->take(12)->get(['id', 'title', 'slug', 'thumbnail', 'type', 'year', 'chapters_count', 'views'])
        );

        $recentChapters = Cache::remember('manga_home_recent_chapters', self::CACHE_TTL / 2, fn () => Chapter::with('manga:id,title,slug,thumbnail')->latest()->take(24)->get()
        );

        $newManga = Cache::remember('manga_home_new', self::CACHE_TTL, fn () => Manga::latest()->take(20)->get(['id', 'title', 'slug', 'thumbnail', 'type', 'year', 'chapters_count', 'views'])
        );

        $mostViewed = Cache::remember('manga_home_most_viewed', self::CACHE_TTL, fn () => Manga::orderBy('views', 'desc')->take(10)->get(['id', 'title', 'slug', 'thumbnail', 'type', 'year', 'chapters_count', 'views'])
        );

        return view('manga-home', compact('featured', 'trending', 'recentChapters', 'newManga', 'mostViewed'));
    }
}
