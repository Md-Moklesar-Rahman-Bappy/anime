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
            | LOAD MANGA (OPTIMIZED)
            |--------------------------------------------------------------------------
            */
            $manga = Manga::query()
                ->where('slug', $slug)
                ->select([
                    'id',
                    'title',
                    'slug',
                    'description',
                    'thumbnail',
                    'banner',
                    'type',
                    'status',
                    'year',
                    'rating',
                    'score',
                    'views',
                ])
                ->with([
                    'genres:id,name,slug',

                    'chapters' => function ($q) {
                        $q->select('id', 'manga_id', 'number', 'title')
                            ->orderByDesc('number')
                            ->limit(50); // ✅ safe limit
                    },
                ])
                ->withCount('chapters')
                ->firstOrFail();


            /*
            |--------------------------------------------------------------------------
            | RELATED MANGA (FAST & CLEAN)
            |--------------------------------------------------------------------------
            */
            $related = Manga::query()
                ->where('id', '!=', $manga->id)
                ->select('id', 'title', 'slug', 'thumbnail', 'views')
                ->orderByDesc('views')
                ->limit(8)
                ->get();


            /*
            |--------------------------------------------------------------------------
            | FAVORITE STATE
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
            | OPTIONAL: INCREMENT VIEW COUNT (SAFE)
            |--------------------------------------------------------------------------
            */
            $manga->increment('views');


            /*
            |--------------------------------------------------------------------------
            | RESPONSE
            |--------------------------------------------------------------------------
            */
            return view('manga-detail', [
                'manga'        => $manga,
                'related'      => $related,
                'isFavorited'  => $isFavorited,
            ]);
        } catch (\Throwable $e) {

            Log::error('Manga detail failed', [
                'slug'  => $slug,
                'error' => $e->getMessage(),
            ]);

            abort(404);
        }
    }
}
