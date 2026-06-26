<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Episode;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        $featured = Cache::remember('home_featured', 600, function () {
            return Anime::where('featured', true)->orderBy('featured_order')->take(5)->get();
        });

        $latestEpisodes = Cache::remember('home_latest_episodes', 300, function () {
            return Episode::with(['anime'])->latest()->take(12)->get();
        });

        $newAnime = Cache::remember('home_new_anime', 300, function () {
            return Anime::latest()->take(10)->get();
        });

        $trending = Cache::remember('home_trending', 600, function () {
            return Anime::orderBy('views', 'desc')->take(10)->get();
        });

        $ongoing = Cache::remember('home_ongoing', 300, function () {
            return Anime::where('status', 'Ongoing')->latest()->take(8)->get();
        });

        return view('home', compact('featured', 'latestEpisodes', 'newAnime', 'trending', 'ongoing'));
    }
}
