<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Episode;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    protected const CACHE_TTL = 300;

    public function index()
    {
        $data = Cache::remember('home_page_data', self::CACHE_TTL, function () {
            $featured = Anime::where('featured', true)->orderBy('featured_order')->take(5)->get();
            $latestEpisodes = Episode::with('anime:id,title,slug,thumbnail')->latest()->take(12)->get();
            $newAnime = Anime::latest()->take(10)->get(['id', 'title', 'slug', 'thumbnail', 'type', 'year', 'episodes_count', 'views']);
            $trending = Anime::orderBy('views', 'desc')->take(10)->get(['id', 'title', 'slug', 'thumbnail', 'type', 'year', 'episodes_count', 'views']);
            $ongoing = Anime::where('status', 'Ongoing')->latest()->take(8)->get(['id', 'title', 'slug', 'thumbnail', 'type', 'year', 'episodes_count', 'views']);

            return compact('featured', 'latestEpisodes', 'newAnime', 'trending', 'ongoing');
        });

        return view('home', $data);
    }
}
