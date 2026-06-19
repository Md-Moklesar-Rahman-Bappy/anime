<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenreController extends Controller
{
    public function show(string $slug)
    {
        try {
            // ✅ Cache genre + anime list
            $cacheKey = "genre_page_{$slug}";

            [$genre, $animeList] = Cache::remember($cacheKey, 300, function () use ($slug) {

                $genre = Genre::where('slug', $slug)->firstOrFail();

                $animeList = $genre->anime()
                    ->with(['genres']) // ✅ eager load
                    ->latest()
                    ->paginate(24);

                return [$genre, $animeList];
            });

            return view('genre', [
                'genre' => $genre,
                'animeList' => $animeList,
            ]);

        } catch (\Throwable $e) {
            Log::error('Genre page load failed', [
                'slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            abort(404, 'Genre not found.');
        }
    }
}