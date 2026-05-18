<?php

namespace App\Http\Controllers;

use App\Models\Manga;

class MangaController extends Controller
{
    public function __invoke($slug)
    {
        $manga = Manga::where('slug', $slug)
            ->with(['genres', 'chapters' => function ($q) {
                $q->orderBy('number', 'desc');
            }])
            ->firstOrFail();

        $key = "manga_view_{$manga->id}";
        if (! session()->has($key)) {
            $manga->increment('views');
            session()->put($key, true);
        }

        $related = Manga::whereHas('genres', function ($q) use ($manga) {
            $q->whereIn('manga_genre_relation.manga_genre_id', $manga->genres->pluck('id'));
        })
            ->where('id', '!=', $manga->id)
            ->take(8)
            ->get();

        $isFavorited = auth()->check() && $manga->favoritedBy()->where('user_id', auth()->id())->exists();

        return view('manga-detail', compact('manga', 'related', 'isFavorited'));
    }
}
