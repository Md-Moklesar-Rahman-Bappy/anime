<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function show(Request $request, string $slug)
    {
        try {
            /*
            |--------------------------------------------------------------------------
            | Genre (Cache Only Static Data)
            |--------------------------------------------------------------------------
            */
            $genre = cache()->remember(
                "genre_{$slug}",
                now()->addMinutes(10),
                fn() => Genre::where('slug', $slug)
                    ->select('id', 'name', 'slug')
                    ->firstOrFail()
            );

            /*
            |--------------------------------------------------------------------------
            | Anime List (NO CACHE - because pagination)
            |--------------------------------------------------------------------------
            */
            $animeList = $genre->anime()
                ->select('anime.id', 'title', 'slug', 'thumbnail', 'views')
                ->with('genres:id,name,slug')
                ->latest()
                ->paginate(24);

            /*
            |--------------------------------------------------------------------------
            | RESPONSE
            |--------------------------------------------------------------------------
            */
            return view('genre', [
                'genre' => $genre,
                'animeList' => $animeList,
            ]);
        } catch (\Throwable $e) {

            $this->logError('Genre page load failed', $e, [
                'slug' => $slug,
            ]);

            abort(404, 'Genre not found.');
        }
    }
}
