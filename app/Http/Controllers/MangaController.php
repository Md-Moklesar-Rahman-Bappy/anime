<?php

namespace App\Http\Controllers;

use App\Models\Manga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MangaController extends Controller
{
    public function __invoke(Request $request, string $slug)
    {
        try {
            /*
            |--------------------------------------------------------------------------
            | Load Manga (Optimized)
            |--------------------------------------------------------------------------
            */
            $manga = Manga::where('slug', $slug)
                ->with([
                    'genres:id,name,slug',
                    'chapters' => fn($q) => $q->orderByDesc('number')->limit(50),
                ])
                ->withCount('chapters')
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | Related Manga
            |--------------------------------------------------------------------------
            */
            $related = Manga::where('id', '!=', $manga->id)
                ->latest()
                ->take(8)
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Favorite State
            |--------------------------------------------------------------------------
            */
            $isFavorited = false;

            if ($request->user()) {
                $isFavorited = $request->user()
                    ->mangaFavorites()
                    ->where('manga_id', $manga->id)
                    ->exists();
            }

            /*
            |--------------------------------------------------------------------------
            | Response
            |--------------------------------------------------------------------------
            */
            return view('manga-detail', [
                'manga' => $manga,
                'related' => $related,
                'isFavorited' => $isFavorited,
            ]);
        } catch (\Throwable $e) {

            Log::error('Manga detail failed', [
                'slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            abort(404);
        }
    }
}
