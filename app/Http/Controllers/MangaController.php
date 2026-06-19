<?php

namespace App\Http\Controllers;

use App\Models\Manga;
<<<<<<< HEAD
=======
use Illuminate\Support\Facades\Log;
>>>>>>> e49809d9d6911bdf67fad69ca28d173fa3ca9407

class MangaController extends Controller
{
    public function __invoke(string $slug)
    {
<<<<<<< HEAD
        $manga = Manga::where('slug', $slug)->firstOrFail();

        return view('manga.show', compact('manga'));
    }
}
=======
        try {
            $manga = Manga::where('slug', $slug)
                ->with([
                    'genres:id,name,slug',
                    'chapters' => fn($q) => $q->orderBy('number'),
                ])
                ->withCount('chapters')
                ->firstOrFail();

            $related = Manga::where('id', '!=', $manga->id)
                ->latest()
                ->take(8)
                ->get();

            $isFavorited = false;

            if (auth()->check() && method_exists(auth()->user(), 'mangaFavorites')) {
                $isFavorited = auth()->user()
                    ->mangaFavorites()
                    ->where('manga_id', $manga->id)
                    ->exists();
            }

            return view('manga-detail', compact(
                'manga',
                'related',
                'isFavorited'
            ));
        } catch (\Throwable $e) {
            Log::error('Manga detail failed', [
                'slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            abort(404);
        }
    }
}
>>>>>>> e49809d9d6911bdf67fad69ca28d173fa3ca9407
