<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Episode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    const TTL = 300;
    const SHORT_TTL = 120;

    protected array $selectFields = [
        'id',
        'title',
        'slug',
        'thumbnail',
        'banner',
        'type',
        'status',
        'year',
        'rating',
        'age_rating',
        'episodes_count',
        'views',
        'description'
    ];

    public function index()
    {
        try {

            $data = Cache::remember('home_all', self::TTL, function () {

                $featured = Anime::where('featured', true)
                    ->orderBy('featured_order')
                    ->take(5)
                    ->get();

                $newAnime = Anime::with('genres')
                    ->latest()
                    ->take(10)
                    ->get($this->selectFields);

                $trending = Anime::orderByDesc('views')
                    ->take(10)
                    ->get($this->selectFields);

                $ongoing = Anime::with('genres')
                    ->where('status', 'Ongoing')
                    ->latest()
                    ->take(8)
                    ->get($this->selectFields);

                $upcoming = Anime::where('status', 'Not Yet Aired')
                    ->latest()
                    ->take(12)
                    ->get($this->selectFields);

                $completed = Anime::where('status', 'Completed')
                    ->latest()
                    ->take(5)
                    ->get($this->selectFields);

                // ✅ FIX ALL MISSING VARIABLES
                $newlyAdded = $newAnime;
                $justCompleted = $completed;
                $topAnime = $trending;

                return compact(
                    'featured',
                    'newAnime',
                    'trending',
                    'ongoing',
                    'upcoming',
                    'completed',
                    'newlyAdded',
                    'justCompleted',
                    'topAnime'
                );
            });

<<<<<<< HEAD
            // Latest Episodes
=======
>>>>>>> e49809d9d6911bdf67fad69ca28d173fa3ca9407
            $latestEpisodes = Cache::remember(
                'home_latest_episodes',
                self::SHORT_TTL,
                fn() => Episode::with('anime:id,title,slug,thumbnail,type')
                    ->latest()
                    ->take(12)
                    ->get()
            );

            // ✅ FIXED VARIABLES FOR VIEW
            $newlyAdded = $data['newAnime'];
            $justCompleted = $data['completed'];
            $topAnime = $data['trending'];

            return view('home', [
                ...$data,
                'latestEpisodes' => $latestEpisodes,
                'newlyAdded' => $newlyAdded,
                'justCompleted' => $justCompleted,
                'topAnime' => $topAnime,
            ]);
        } catch (\Throwable $e) {

            Log::error('Homepage load failed', [
                'error' => $e->getMessage(),
            ]);

            return view('home', [
                'featured' => collect(),
                'latestEpisodes' => collect(),
                'newAnime' => collect(),
<<<<<<< HEAD
                'newlyAdded' => collect(),
=======
>>>>>>> e49809d9d6911bdf67fad69ca28d173fa3ca9407
                'trending' => collect(),
                'ongoing' => collect(),
                'upcoming' => collect(),
                'completed' => collect(),
<<<<<<< HEAD
=======
                'newlyAdded' => collect(),
>>>>>>> e49809d9d6911bdf67fad69ca28d173fa3ca9407
                'justCompleted' => collect(),
                'topAnime' => collect(),
            ]);
        }
    }
}
