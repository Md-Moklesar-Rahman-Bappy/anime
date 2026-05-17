<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Comment;
use Illuminate\Http\Request;

class WatchController extends Controller
{
    public function __invoke($slug)
    {
        $anime = Anime::where('slug', $slug)->with(['episodes' => function ($q) {
            $q->orderBy('number');
        }, 'genres'])->firstOrFail();

        $anime->increment('views');

        $episode = request('ep')
            ? $anime->episodes->where('number', request('ep'))->first()
            : $anime->episodes->first();

        if (!$episode) {
            abort(404);
        }

        $episode->load(['servers', 'skipTimes']);

        $prevEpisode = $anime->episodes->where('number', $episode->number - 1)->first();
        $nextEpisode = $anime->episodes->where('number', $episode->number + 1)->first();

        $comments = Comment::where('episode_id', $episode->id)
            ->with('user')->latest()->paginate(20);

        $related = Anime::whereHas('genres', function ($q) use ($anime) {
            $q->whereIn('genres.id', $anime->genres->pluck('id'));
        })->where('id', '!=', $anime->id)->inRandomOrder()->take(8)->get();

        return view('watch', compact(
            'anime', 'episode', 'prevEpisode', 'nextEpisode',
            'comments', 'related'
        ));
    }
}
