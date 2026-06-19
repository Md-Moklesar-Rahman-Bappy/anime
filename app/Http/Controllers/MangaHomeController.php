<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Manga;
use Illuminate\Support\Facades\Cache;

class MangaHomeController extends Controller
{
    protected const TTL = 300;
    protected const SHORT_TTL = 120;

    protected array $fields = [
        'id',
        'title',
        'slug',
        'thumbnail',
        'type',
        'year',
        'chapters_count',
        'views',
    ];

    public function index()
    {
        try {
            /*
            |--------------------------------------------------------------------------
            | MAIN DATA (CACHED)
            |--------------------------------------------------------------------------
            */
            $data = Cache::remember('manga_home_all', self::TTL, function () {

                $featured = Manga::where('featured', true)
                    ->orderBy('featured_order')
                    ->take(5)
                    ->get($this->fields);

                $trending = Manga::orderByDesc('views')
                    ->take(12)
                    ->get($this->fields);

                $newManga = Manga::with('genres:id,name,slug')
                    ->latest()
                    ->take(20)
                    ->get($this->fields);

                return compact(
                    'featured',
                    'trending',
                    'newManga'
                );
            });

            /*
            |--------------------------------------------------------------------------
            | RECENT CHAPTERS (SHORT CACHE)
            |--------------------------------------------------------------------------
            */
            $recentChapters = Cache::remember(
                'manga_home_recent_chapters',
                self::SHORT_TTL,
                fn() => Chapter::with('manga:id,title,slug,thumbnail')
                    ->select('id', 'manga_id', 'number', 'title', 'created_at')
                    ->latest('created_at')
                    ->take(24)
                    ->get()
            );

            /*
            |--------------------------------------------------------------------------
            | RESPONSE
            |--------------------------------------------------------------------------
            */
            return view('manga-home', [
                ...$data,
                'recentChapters' => $recentChapters,
            ]);
        } catch (\Throwable $e) {

            $this->logError('Manga homepage failed', $e);

            /*
            |--------------------------------------------------------------------------
            | SAFE FALLBACK
            |--------------------------------------------------------------------------
            */
            return view('manga-home', [
                'featured' => collect(),
                'trending' => collect(),
                'newManga' => collect(),
                'recentChapters' => collect(),
            ]);
        }
    }
}
