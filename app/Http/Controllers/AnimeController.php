<?php

namespace App\Http\Controllers;

use App\Models\Anime;
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
            $anime = Anime::where('slug', $slug)
                ->with([
                    'genres:id,name,slug',
                    'episodes' => fn($q) => $q->orderBy('number')
                ])
                ->withCount('episodes')
                ->firstOrFail();

            $this->viewCounter->increment($anime, 'anime');

            $related = $this->relatedContent->byGenres(
                $anime,
                $anime->genres ?? collect(),
                'genres'
            );

            $isFavorited = false;

            if (auth()->check()) {
                $isFavorited = auth()->user()
                    ->favorites()
                    ->where('anime_id', $anime->id)
                    ->exists();
            }

            return view('anime-detail', compact(
                'anime',
                'related',
                'isFavorited'
            ));
        } catch (\Throwable $e) {
            Log::error('Anime detail failed', [
                'slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            abort(404);
        }
    }
}
