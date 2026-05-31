<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Manga;
use Illuminate\Support\Facades\Cache;

class MangaHomeController extends Controller
{
    protected const CACHE_TTL = 300;

    public function index()
    {
        $data = Cache::remember('manga_home_page_data', self::CACHE_TTL, function () {
            $featured = Manga::where('featured', true)->orderBy('featured_order')->take(5)->get();
            $trending = Manga::orderBy('views', 'desc')->take(12)->get(['id', 'title', 'slug', 'thumbnail', 'type', 'year', 'chapters_count', 'views']);
            $recentChapters = Chapter::with('manga:id,title,slug,thumbnail')->latest()->take(24)->get();
            $newManga = Manga::latest()->take(20)->get(['id', 'title', 'slug', 'thumbnail', 'type', 'year', 'chapters_count', 'views']);
            $mostViewed = Manga::orderBy('views', 'desc')->take(10)->get(['id', 'title', 'slug', 'thumbnail', 'type', 'year', 'chapters_count', 'views']);

            return compact('featured', 'trending', 'recentChapters', 'newManga', 'mostViewed');
        });

        return view('manga-home', $data);
    }
}
