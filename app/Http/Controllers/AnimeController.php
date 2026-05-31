<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Favorite;
use App\Services\RelatedContentService;
use App\Services\ViewCounterService;

class AnimeController extends Controller
{
    public function __construct(
        protected ViewCounterService $viewCounter,
        protected RelatedContentService $relatedContent,
    ) {}

    public function __invoke($slug)
    {
        $anime = Anime::where('slug', $slug)
            ->with(['genres', 'episodes' => fn($q) => $q->orderBy('number')])
            ->withCount('episodes')
            ->firstOrFail();

        $this->viewCounter->increment($anime, 'anime');

        $related = $this->relatedContent->byGenres($anime, $anime->genres, 'genres');

        $isFavorited = auth()->check()
            && Favorite::where('user_id', auth()->id())
                ->where('anime_id', $anime->id)
                ->exists();

        return view('anime-detail', compact('anime', 'related', 'isFavorited'));
    }
}
