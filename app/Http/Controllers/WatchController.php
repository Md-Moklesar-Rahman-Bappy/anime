<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Comment;
use App\Models\Favorite;
use Illuminate\Support\Facades\Storage;

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

        if (! $episode) {
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

        $isFavorited = false;
        $favCategory = null;
        if (auth()->check()) {
            $fav = Favorite::where('user_id', auth()->id())
                ->where('anime_id', $anime->id)->first();
            $isFavorited = (bool) $fav;
            $favCategory = $fav?->category;
        }

        // Build all servers with language
        $allServers = [];
        $youtubeServer = $episode->servers->firstWhere('type', 'youtube');
        $videoServers = $episode->servers->where('type', '!=', 'youtube');
        $hasServers = $videoServers->count() > 0;
        $hasVideoPath = ! empty($episode->video_path);
        $ytInVideoPath = false;
        $ytVideoId = null;

        if ($hasVideoPath && ! $youtubeServer) {
            if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/', $episode->video_path, $m)) {
                $ytInVideoPath = true;
                $ytVideoId = $m[1];
            }
        }

        if ($youtubeServer) {
            $allServers[] = ['id' => 'youtube', 'label' => 'YouTube', 'url' => $youtubeServer->url, 'type' => 'youtube', 'language' => $youtubeServer->language];
        } elseif ($ytInVideoPath) {
            $allServers[] = ['id' => 'youtube', 'label' => 'YouTube', 'url' => 'https://www.youtube.com/watch?v='.$ytVideoId, 'type' => 'youtube', 'language' => 'english'];
        }

        $idx = 0;
        foreach ($videoServers as $s) {
            $idx++;
            $allServers[] = ['id' => $s->id, 'label' => $s->label ?? 'Server '.$idx, 'url' => $s->url, 'type' => $s->type, 'language' => $s->language];
        }

        if (! $hasServers && $hasVideoPath && ! $ytInVideoPath) {
            $videoSrc = str_starts_with($episode->video_path, 'http') ? $episode->video_path : Storage::url($episode->video_path);
            $allServers[] = ['id' => 'local', 'label' => 'Default', 'url' => $videoSrc, 'type' => 'mp4', 'language' => 'english'];
        }

        $skipTimes = $episode->skipTimes->first();

        // Group servers by language
        $languageGroups = collect($allServers)->groupBy('language');
        $languages = $languageGroups->keys()->values()->toArray();

        $initialServer = $allServers[0] ?? null;
        $isYoutubeInit = $initialServer && $initialServer['type'] === 'youtube';

        return view('watch', compact(
            'anime', 'episode', 'prevEpisode', 'nextEpisode',
            'comments', 'related', 'isFavorited', 'favCategory',
            'allServers', 'languageGroups', 'languages', 'initialServer',
            'isYoutubeInit', 'skipTimes'
        ));
    }
}
