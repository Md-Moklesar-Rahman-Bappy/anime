<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Comment;
use App\Models\WatchHistory;
use App\Services\RelatedContentService;
use App\Services\ServerResolverService;
use App\Services\ViewCounterService;
use Illuminate\Http\Request;

class WatchController extends Controller
{
    public function __construct(
        protected ViewCounterService $viewCounter,
        protected RelatedContentService $relatedContent,
        protected ServerResolverService $serverResolver,
    ) {}

    public function index(Request $request, string $slug)
    {
        try {
            $user = $request->user();
            $epNumber = (int) $request->query('ep');

            /*
            |--------------------------------------------------------------------------
            | Load Anime (LIGHTWEIGHT)
            |--------------------------------------------------------------------------
            */
            $anime = Anime::where('slug', $slug)
                ->with('genres:id,name,slug')
                ->firstOrFail();

            $this->viewCounter->increment($anime, 'anime');

            /*
            |--------------------------------------------------------------------------
            | Resolve Episode (NO COLLECTION LOAD)
            |--------------------------------------------------------------------------
            */
            $episode = $anime->episodes()
                ->where('number', $epNumber)
                ->select('id', 'anime_id', 'number', 'title', 'thumbnail', 'has_sub', 'has_dub')
                ->first()
                ?? $anime->episodes()
                ->orderBy('number')
                ->first();

            if (!$episode) {
                abort(404);
            }

            /*
            |--------------------------------------------------------------------------
            | Load Episode Relations
            |--------------------------------------------------------------------------
            */
            $episode->load(['servers', 'skipTimes']);

            /*
            |--------------------------------------------------------------------------
            | Navigation (MODEL METHODS)
            |--------------------------------------------------------------------------
            */
            $prevEpisode = $episode->previous();
            $nextEpisode = $episode->next();

            /*
            |--------------------------------------------------------------------------
            | Favorite State
            |--------------------------------------------------------------------------
            */
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

            /*
            |--------------------------------------------------------------------------
            | Server Resolution
            |--------------------------------------------------------------------------
            */
            $serverData = $this->serverResolver->resolveAll($episode) ?? [];

            /*
            |--------------------------------------------------------------------------
            | Watch History
            |--------------------------------------------------------------------------
            */
            $watchHistory = null;

            if ($user) {
                $watchHistory = WatchHistory::where('user_id', $user->id)
                    ->where('episode_id', $episode->id)
                    ->first();
            }

            /*
            |--------------------------------------------------------------------------
            | Comments (OPTIMIZED)
            |--------------------------------------------------------------------------
            */
            $comments = Comment::with('user:id,name')
                ->where('episode_id', $episode->id)
                ->latest()
                ->paginate(20);

            /*
            |--------------------------------------------------------------------------
            | Related Anime
            |--------------------------------------------------------------------------
            */
            $related = $this->relatedContent->byGenres(
                $anime,
                $anime->genres ?? collect(),
                'genres'
            );

            /*
            |--------------------------------------------------------------------------
            | RESPONSE
            |--------------------------------------------------------------------------
            */
            return view('watch', array_merge([
                'anime' => $anime,
                'episode' => $episode,
                'prevEpisode' => $prevEpisode,
                'nextEpisode' => $nextEpisode,
                'comments' => $comments,
                'related' => $related,
                'isFavorited' => $isFavorited,
                'favCategory' => $favCategory,

                // ✅ Player data
                'watchProgress' => $watchHistory?->progress ?? 0,
                'isCompleted' => $watchHistory?->completed ?? false,
                'skipTimes' => $episode->skipTimes->first(),
            ], $serverData));
        } catch (\Throwable $e) {

            $this->logError('Watch page failed', $e, [
                'slug' => $slug,
                'ep' => $request->query('ep'),
            ]);

            abort(404, 'Video not found');
        }
    }
}
