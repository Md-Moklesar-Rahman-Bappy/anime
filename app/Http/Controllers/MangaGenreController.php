<?php

namespace App\Http\Controllers;

use App\Models\MangaGenre;
use Illuminate\Http\Request;

class MangaGenreController extends Controller
{
    public function show(Request $request, string $slug)
    {
        try {
            /*
            |--------------------------------------------------------------------------
            | Genre (CACHE SAFE)
            |--------------------------------------------------------------------------
            */
            $genre = cache()->remember(
                "manga_genre_{$slug}",
                now()->addMinutes(10),
                fn() => MangaGenre::where('slug', $slug)
                    ->select('id', 'name', 'slug')
                    ->firstOrFail()
            );

            /*
            |--------------------------------------------------------------------------
            | Manga List (NO CACHE - pagination safe)
            |--------------------------------------------------------------------------
            */
            $mangaList = $genre->manga()
                ->select('manga.id', 'title', 'slug', 'thumbnail', 'views')
                ->with('genres:id,name,slug')
                ->latest()
                ->paginate(24)
                ->withQueryString();

            /*
            |--------------------------------------------------------------------------
            | RESPONSE
            |--------------------------------------------------------------------------
            */
            return view('manga-list', [
                'mangaList' => $mangaList,
                'title' => $genre->name,
            ]);
        } catch (\Throwable $e) {

            $this->logError('Manga genre page failed', $e, [
                'slug' => $slug,
            ]);

            abort(404, 'Genre not found.');
        }
    }
}
