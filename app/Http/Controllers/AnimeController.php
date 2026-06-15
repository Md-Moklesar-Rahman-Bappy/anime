<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Favorite;
use App\Services\RelatedContentService;
use App\Services\ViewCounterService;
use Illuminate\Support\Facades\Log;

class AnimeController extends Controller
{
    public function __construct(
        protected ViewCounterService $viewCounter,
        protected RelatedContentService $relatedContent,
    ) {}

    public function __invoke(string $slug)
    {
        try {
            // ✅ Load anime with optimized relations
            $anime = Anime::where('slug', $slug)
                ->with([
                    'genres:id,name,slug',
                    'episodes' => fn ($q) => $q->orderBy('number')
                ])
                ->withCount('episodes')
                ->firstOrFail();

            // ✅ Increment views safely
            $this->viewCounter->increment($anime, 'anime');

            // ✅ Related content
            $related = $this->relatedContent->byGenres(
                $anime,
                $anime->genres ?? collect(),
                'genres'
            );

            // ✅ Favorite check (cleaner)
            $isFavorited = false;

            if (auth()->check()) {
                $isFavorited = auth()->user()
                    ->favorites()
                    ->where('anime_id', $anime->id)
                    ->exists();
            }

            return view('anime-detail', [
                'anime' => $anime,
                'related' => $related,
                'isFavorited' => $isFavorited,
            ]);

        } catch (\Throwable $e) {
            Log::error('Anime detail load failed', [
                'slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            abort(404, 'Anime not found.');
        }
    }
}