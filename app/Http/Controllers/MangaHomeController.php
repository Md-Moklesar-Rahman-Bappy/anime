<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Manga;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MangaHomeController extends Controller
{
    const TTL = 300;
    const SHORT_TTL = 120;

    protected array $fields = [
        'id', 'title', 'slug', 'thumbnail',
        'type', 'year', 'chapters_count', 'views'
    ];

    public function index()
    {
        try {
            $data = Cache::remember('manga_home_all', self::TTL, function () {

                $featured = Manga::where('featured', true)
                    ->orderBy('featured_order')
                    ->take(5)
                    ->get();

                $trending = Manga::orderByDesc('views')
                    ->take(12)
                    ->get($this->fields);

                $newManga = Manga::with('genres')
                    ->latest()
                    ->take(20)
                    ->get($this->fields);

                return compact(
                    'featured',
                    'trending',
                    'newManga'
                );
            });

            // ✅ frequently updated data
            $recentChapters = Cache::remember(
                'manga_home_recent_chapters',
                self::SHORT_TTL,
                fn () => Chapter::with('manga:id,title,slug,thumbnail')
                    ->latest()
                    ->take(24)
                    ->get()
            );

            return view('manga-home', [
                ...$data,
                'recentChapters' => $recentChapters,
            ]);

        } catch (\Throwable $e) {
            Log::error('Manga homepage failed', [
                'error' => $e->getMessage(),
            ]);

            return view('manga-home', [
                'featured' => [],
                'trending' => [],
                'newManga' => [],
                'recentChapters' => [],
            ]);
        }
    }
}