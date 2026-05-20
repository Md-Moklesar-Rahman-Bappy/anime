<?php

namespace App\Http\Controllers;

use App\Models\Manga;
use Illuminate\Support\Facades\Cache;

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

        $related = Cache::remember('related_manga_'.$manga->id, 600, function () use ($manga) {
            $genreIds = $manga->genres->pluck('id')->toArray();
            if (empty($genreIds)) {
                return collect();
            }
            return Manga::whereHas('genres', function ($q) use ($genreIds) {
                $q->whereIn('manga_genre_relation.manga_genre_id', $genreIds);
            }, '>=', count($genreIds))
                ->where('id', '!=', $manga->id)
                ->orderBy('views', 'desc')
                ->take(8)
                ->get();
        });

        $isFavorited = auth()->check() && $manga->favoritedBy()->where('user_id', auth()->id())->exists();

        return view('manga-detail', compact('manga', 'related', 'isFavorited'));
    }
}
