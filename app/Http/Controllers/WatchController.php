<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Comment;
use App\Models\Favorite;
use App\Services\RelatedContentService;
use App\Services\ServerResolverService;
use App\Services\ViewCounterService;

class WatchController extends Controller
{
    public function __construct(
        protected ViewCounterService $viewCounter,
        protected RelatedContentService $relatedContent,
        protected ServerResolverService $serverResolver,
    ) {}

    public function __invoke($slug)
    {
        $anime = $this->loadAnime($slug);
        $this->viewCounter->increment($anime, 'anime');

        $episode = $this->resolveEpisode($anime);
        $episode->load(['servers', 'skipTimes']);

        $isFavorited = false;
        $favCategory = null;

        if (auth()->check()) {
            $fav = Favorite::where('user_id', auth()->id())
                ->where('anime_id', $anime->id)->first();
            $isFavorited = (bool) $fav;
            $favCategory = $fav?->category;
        }

        $serverData = $this->serverResolver->resolveAll($episode);

        return view('watch', array_merge([
            'anime' => $anime,
            'episode' => $episode,
            'prevEpisode' => $this->getSiblingEpisode($anime, $episode, -1),
            'nextEpisode' => $this->getSiblingEpisode($anime, $episode, 1),
            'comments' => Comment::where('episode_id', $episode->id)
                ->with('user')->latest()->paginate(20),
            'related' => $this->relatedContent->byGenres($anime, $anime->genres, 'genres'),
            'isFavorited' => $isFavorited,
            'favCategory' => $favCategory,
        ], $serverData));
    }

    protected function loadAnime(string $slug): Anime
    {
        return Anime::where('slug', $slug)
            ->with(['genres'])
            ->with(['episodes' => fn($q) => $q->select('id', 'anime_id', 'number', 'title', 'thumbnail', 'has_sub', 'has_dub')
                ->orderBy('number')])
            ->firstOrFail();
    }

    protected function resolveEpisode(Anime $anime)
    {
        $episode = request('ep')
            ? $anime->episodes->firstWhere('number', (int) request('ep'))
            : $anime->episodes->first();

        abort_if(!$episode, 404);

        return $episode;
    }

    protected function getSiblingEpisode(Anime $anime, $episode, int $direction)
    {
        return $anime->episodes->where('number', $episode->number + $direction)->first();
    }
}
