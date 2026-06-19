<?php

namespace App\Http\Controllers;

use App\Models\Manga;
use Illuminate\Http\Request;

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
                    'chapters' => fn($q) =>
                    $q->select('id', 'manga_id', 'number', 'title')
                        ->orderByDesc('number')
                        ->limit(50), // ✅ prevent heavy load
                ])
                ->withCount('chapters')
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | Related Manga (Optimized)
            |--------------------------------------------------------------------------
            */
            $related = Manga::where('id', '!=', $manga->id)
                ->select('id', 'title', 'slug', 'thumbnail', 'views')
                ->orderByDesc('views') // ✅ better than latest()
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

            $this->logError('Manga detail failed', $e, [
                'slug' => $slug,
            ]);

            abort(404);
        }
    }
}
