<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Services\RelatedContentService;
use App\Services\ViewCounterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnimeController extends Controller
{
    public function __construct(
        protected ViewCounterService $viewCounter,
        protected RelatedContentService $relatedContent,
    ) {}

    public function show(Request $request, string $slug)
    {
        try {
            /*
            |--------------------------------------------------------------------------
            | Load Anime (Optimized)
            |--------------------------------------------------------------------------
            */
            $anime = Anime::where('slug', $slug)
                ->with([
                    'genres:id,name,slug',
                    // ✅ load only needed episode data (lightweight)
                    'episodes' => fn($q) =>
                    $q->select('id', 'anime_id', 'number', 'title', 'thumbnail', 'has_sub', 'has_dub')
                        ->orderBy('number'),
                ])
                ->withCount('episodes')
                ->firstOrFail();

            $this->viewCounter->increment($anime, 'anime');

            /*
            |--------------------------------------------------------------------------
            | Related Anime
            |--------------------------------------------------------------------------
            */
            $related = $this->relatedContent->byGenres(
                $anime,
                $anime->genres ?? collect(),
                'genres'
            );

            /*
            |--------------------------------------------------------------------------
            | Favorite State
            |--------------------------------------------------------------------------
            */
            $isFavorited = false;

            if ($request->user()) {
                $isFavorited = $request->user()
                    ->favorites()
                    ->where('anime_id', $anime->id)
                    ->exists();
            }

            /*
            |--------------------------------------------------------------------------
            | Response
            |--------------------------------------------------------------------------
            */
            return view('anime-detail', [
                'anime' => $anime,
                'related' => $related,
                'isFavorited' => $isFavorited,
            ]);
        } catch (\Throwable $e) {

            Log::error('Anime detail failed', [
                'slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            abort(404);
        }
    }
}
