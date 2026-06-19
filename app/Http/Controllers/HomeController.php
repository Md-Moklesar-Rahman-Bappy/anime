<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Episode;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    protected const TTL = 300;
    protected const SHORT_TTL = 120;

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
        'description',
    ];

    public function index()
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | MAIN HOME DATA (CACHED)
            |--------------------------------------------------------------------------
            */
            $data = Cache::remember('home_all', self::TTL, function () {

                $featured = Anime::where('featured', true)
                    ->orderBy('featured_order')
                    ->take(5)
                    ->get($this->selectFields);

                $newAnime = Anime::with('genres:id,name,slug')
                    ->latest()
                    ->take(10)
                    ->get($this->selectFields);

                $trending = Anime::orderByDesc('views')
                    ->take(10)
                    ->get($this->selectFields);

                $ongoing = Anime::with('genres:id,name,slug')
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

                return compact(
                    'featured',
                    'newAnime',
                    'trending',
                    'ongoing',
                    'upcoming',
                    'completed'
                );
            });

            /*
            |--------------------------------------------------------------------------
            | LATEST EPISODES (SHORT CACHE)
            |--------------------------------------------------------------------------
            */
            $latestEpisodes = Cache::remember(
                'home_latest_episodes',
                self::SHORT_TTL,
                fn() => Episode::with('anime:id,title,slug,thumbnail,type')
                    ->select('id', 'anime_id', 'number', 'title', 'thumbnail', 'created_at')
                    ->latest()
                    ->take(12)
                    ->get()
            );

            /*
            |--------------------------------------------------------------------------
            | NORMALIZED VARIABLES (VIEW FRIENDLY)
            |--------------------------------------------------------------------------
            */
            return view('home', [
                ...$data,
                'latestEpisodes' => $latestEpisodes,

                // ✅ aliases
                'newlyAdded' => $data['newAnime'],
                'justCompleted' => $data['completed'],
                'topAnime' => $data['trending'],
            ]);
        } catch (\Throwable $e) {

            $this->logError('Homepage load failed', $e);

            /*
            |--------------------------------------------------------------------------
            | SAFE FALLBACK
            |--------------------------------------------------------------------------
            */
            return view('home', [
                'featured' => collect(),
                'latestEpisodes' => collect(),
                'newAnime' => collect(),
                'trending' => collect(),
                'ongoing' => collect(),
                'upcoming' => collect(),
                'completed' => collect(),
                'newlyAdded' => collect(),
                'justCompleted' => collect(),
                'topAnime' => collect(),
            ]);
        }
    }
}
