<?php

namespace App\Http\Controllers;

use App\Models\MangaGenre;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MangaGenreController extends Controller
{
    public function show(string $slug)
    {
        try {
            $cacheKey = "manga_genre_{$slug}";

            [$genre, $mangaList] = Cache::remember($cacheKey, 300, function () use ($slug) {

                $genre = MangaGenre::where('slug', $slug)->firstOrFail();

                $mangaList = $genre->manga()
                    ->with('genres') // ✅ prevent N+1
                    ->latest()
                    ->paginate(24)
                    ->withQueryString();

                return [$genre, $mangaList];
            });

            return view('manga-list', [
                'mangaList' => $mangaList,
                'title' => $genre->name,
            ]);

        } catch (\Throwable $e) {
            Log::error('Manga genre page failed', [
                'slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            abort(404, 'Genre not found.');
        }
    }
}