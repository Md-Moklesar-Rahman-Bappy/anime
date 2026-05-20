<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Favorite;
use Illuminate\Support\Facades\Cache;

class AnimeController extends Controller
{
    public function __invoke($slug)
    {
        $anime = Anime::where('slug', $slug)->with(['genres', 'episodes' => function ($q) {
            $q->orderBy('number');
        }])->firstOrFail();

        $related = Cache::remember('related_anime_'.$anime->id, 600, function () use ($anime) {
            $genreIds = $anime->genres->pluck('id')->toArray();
            if (empty($genreIds)) {
                return collect();
            }
            return Anime::whereHas('genres', function ($q) use ($genreIds) {
                $q->whereIn('genres.id', $genreIds);
            }, '>=', count($genreIds))
                ->where('id', '!=', $anime->id)
                ->orderBy('views', 'desc')
                ->take(8)
                ->get();
        });

        $isFavorited = auth()->check() && Favorite::where('user_id', auth()->id())
            ->where('anime_id', $anime->id)->exists();
        return view('anime-detail', compact('anime', 'related', 'isFavorited'));
    }
}
