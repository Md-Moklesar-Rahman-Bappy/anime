<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Episode;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    const CACHE_TTL = 300;

    public function __construct(
        protected CacheService $cache,
    ) {}

    public function index()
    {
        $featured = Cache::remember('home_featured', self::CACHE_TTL, fn () => Anime::where('featured', true)->orderBy('featured_order')->take(5)->get()
        );

        $latestEpisodes = Cache::remember('home_latest_episodes', self::CACHE_TTL / 2, fn () => Episode::with('anime:id,title,slug,thumbnail')->latest()->take(12)->get()
        );

        $newAnime = Cache::remember('home_new_anime', self::CACHE_TTL, fn () => Anime::latest()->take(10)->get(['id', 'title', 'slug', 'thumbnail', 'type', 'year', 'episodes_count', 'views'])
        );

        $trending = Cache::remember('home_trending', self::CACHE_TTL, fn () => Anime::orderBy('views', 'desc')->take(10)->get(['id', 'title', 'slug', 'thumbnail', 'type', 'year', 'episodes_count', 'views'])
        );

        $ongoing = Cache::remember('home_ongoing', self::CACHE_TTL, fn () => Anime::where('status', 'Ongoing')->latest()->take(8)->get(['id', 'title', 'slug', 'thumbnail', 'type', 'year', 'episodes_count', 'views'])
        );

        return view('home', compact('featured', 'latestEpisodes', 'newAnime', 'trending', 'ongoing'));
    }
}
