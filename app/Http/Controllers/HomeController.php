<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Episode;
class HomeController extends Controller
{
    public function index()
    {
        $featured = Anime::where('featured', true)->latest()->take(5)->get();
        $latestEpisodes = Episode::with(['anime'])->latest()->take(12)->get();
        $newAnime = Anime::latest()->take(10)->get();
        $trending = Anime::orderBy('views', 'desc')->take(10)->get();
        $ongoing = Anime::where('status', 'Ongoing')->latest()->take(8)->get();
        return view('home', compact('featured', 'latestEpisodes', 'newAnime', 'trending', 'ongoing'));
    }
}
