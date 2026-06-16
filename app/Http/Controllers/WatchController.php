<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Comment;
use App\Services\RelatedContentService;
use App\Services\ServerResolverService;
use App\Services\ViewCounterService;
use Illuminate\Support\Facades\Log;

class WatchController extends Controller
{
    public function __construct(
        protected ViewCounterService $viewCounter,
        protected RelatedContentService $relatedContent,
        protected ServerResolverService $serverResolver,
    ) {}

    public function __invoke(string $slug)
    {
        try {
            $user = auth()->user();

            // ✅ Load anime with optimized relations
            $anime = Anime::where('slug', $slug)
                ->with([
                    'genres:id,name,slug',
                    'episodes:id,anime_id,number,title,thumbnail,has_sub,has_dub'
                ])
                ->firstOrFail();

            $this->viewCounter->increment($anime, 'anime');

            // ✅ Resolve episode
            $epNumber = (int) request('ep');

            $episode = $anime->episodes
                ->firstWhere('number', $epNumber) ?? $anime->episodes->first();

            if (!$episode) {
                abort(404);
            }

            // ✅ Load episode relations
            $episode->load(['servers', 'skipTimes']);

            // ✅ Episode navigation (no extra queries)
            $episodes = $anime->episodes->sortBy('number')->values();
            $index = $episodes->search(fn ($e) => $e->id === $episode->id);

            $prevEpisode = $episodes[$index - 1] ?? null;
            $nextEpisode = $episodes[$index + 1] ?? null;

            // ✅ Favorite state
            $isFavorited = false;
            $favCategory = null;

            if ($user) {
                $fav = $user->favorites()
                    ->where('anime_id', $anime->id)
                    ->first();

                if ($fav) {
                    $isFavorited = true;
                    $favCategory = $fav->category;
                }
            }

            // ✅ Server resolution
            $serverData = $this->serverResolver->resolveAll($episode);

            // ✅ Comments
            $comments = Comment::with('user')
                ->where('episode_id', $episode->id)
                ->latest()
                ->paginate(20);

            // ✅ Related
            $related = $this->relatedContent->byGenres(
                $anime,
                $anime->genres ?? collect(),
                'genres'
            );

            return view('watch', array_merge([
                'anime' => $anime,
                'episode' => $episode,
                'prevEpisode' => $prevEpisode,
                'nextEpisode' => $nextEpisode,
                'comments' => $comments,
                'related' => $related,
                'isFavorited' => $isFavorited,
                'favCategory' => $favCategory,
            ], $serverData));

        } catch (\Throwable $e) {
            Log::error('Watch page failed', [
                'slug' => $slug,
                'ep' => request('ep'),
                'error' => $e->getMessage(),
            ]);

            abort(404, 'Video not found');
        }
    }
}