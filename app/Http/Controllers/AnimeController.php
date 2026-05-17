<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Favorite;

class AnimeController extends Controller
{
    public function __invoke($slug)
    {
        $anime = Anime::where('slug', $slug)->with(['genres', 'episodes' => function ($q) {
            $q->orderBy('number');
        }])->firstOrFail();

        $related = Anime::whereHas('genres', function ($q) use ($anime) {
            $q->whereIn('genres.id', $anime->genres->pluck('id'));
        })->where('id', '!=', $anime->id)->inRandomOrder()->take(8)->get();

        $isFavorited = auth()->check() && Favorite::where('user_id', auth()->id())
            ->where('anime_id', $anime->id)->exists();

        return view('anime-detail', compact('anime', 'related', 'isFavorited'));
    }
}
