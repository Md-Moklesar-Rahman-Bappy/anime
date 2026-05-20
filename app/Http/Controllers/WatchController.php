<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Comment;
use App\Models\Favorite;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class WatchController extends Controller
{
    public function __invoke($slug)
    {
        $anime = $this->loadAnime($slug);
        $this->incrementViews($anime);

        $episode = $this->resolveEpisode($anime);
        $episode->load(['servers', 'skipTimes']);

        $data = [
            'anime' => $anime,
            'episode' => $episode,
            'prevEpisode' => $this->prevEpisode($anime, $episode),
            'nextEpisode' => $this->nextEpisode($anime, $episode),
            'comments' => $this->loadComments($episode),
            'related' => $this->loadRelated($anime),
            'isFavorited' => false,
            'favCategory' => null,
        ];

        if (auth()->check()) {
            $fav = Favorite::where('user_id', auth()->id())
                ->where('anime_id', $anime->id)->first();
            $data['isFavorited'] = (bool) $fav;
            $data['favCategory'] = $fav?->category;
        }

        $serverData = $this->buildServerData($episode);
        $data = array_merge($data, $serverData);

        return view('watch', $data);
    }

    protected function loadAnime(string $slug): Anime
    {
        return Anime::where('slug', $slug)->with(['episodes' => function ($q) {
            $q->orderBy('number');
        }, 'genres'])->firstOrFail();
    }

    protected function incrementViews(Anime $anime): void
    {
        $key = "anime_view_{$anime->id}";
        if (! session()->has($key)) {
            $anime->increment('views');
            session()->put($key, true);
        }
    }

    protected function resolveEpisode(Anime $anime)
    {
        $episode = request('ep')
            ? $anime->episodes->where('number', request('ep'))->first()
            : $anime->episodes->first();

        if (! $episode) {
            abort(404);
        }

        return $episode;
    }

    protected function prevEpisode(Anime $anime, $episode)
    {
        return $anime->episodes->where('number', $episode->number - 1)->first();
    }

    protected function nextEpisode(Anime $anime, $episode)
    {
        return $anime->episodes->where('number', $episode->number + 1)->first();
    }

    protected function loadComments($episode)
    {
        return Comment::where('episode_id', $episode->id)
            ->with('user')->latest()->paginate(20);
    }

    protected function loadRelated(Anime $anime)
    {
        return Cache::remember('related_anime_'.$anime->id, 600, function () use ($anime) {
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
    }

    protected function buildServerData($episode): array
    {
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
            $allServers[] = [
                'server_id' => 'youtube',
                'label' => 'YouTube',
                'url' => $youtubeServer->url,
                'type' => 'youtube',
                'language' => $youtubeServer->language,
            ];
        } elseif ($ytInVideoPath) {
            $allServers[] = [
                'server_id' => 'youtube',
                'label' => 'YouTube',
                'url' => 'https://www.youtube.com/watch?v='.$ytVideoId,
                'type' => 'youtube',
                'language' => 'english',
            ];
        }

        $idx = 0;
        foreach ($videoServers as $s) {
            $idx++;
            $allServers[] = [
                'server_id' => 'video_'.$s->id,
                'label' => $s->label ?? 'Server '.$idx,
                'url' => $s->url,
                'type' => $s->type,
                'language' => $s->language,
            ];
        }

        if (! $hasServers && $hasVideoPath && ! $ytInVideoPath) {
            $videoSrc = str_starts_with($episode->video_path, 'http') ? $episode->video_path : Storage::url($episode->video_path);
            $allServers[] = [
                'server_id' => 'local',
                'label' => 'Default',
                'url' => $videoSrc,
                'type' => 'mp4',
                'language' => 'english',
            ];
        }

        $skipTimes = $episode->skipTimes->first() ?: null;

        $languageGroups = collect($allServers)->groupBy('language');
        $languages = $languageGroups->keys()->values()->toArray();

        $initialServer = $allServers[0] ?? null;
        $isYoutubeInit = $initialServer && $initialServer['type'] === 'youtube';

        return [
            'allServers' => $allServers,
            'languageGroups' => $languageGroups,
            'languages' => $languages,
            'initialServer' => $initialServer,
            'isYoutubeInit' => $isYoutubeInit,
            'skipTimes' => $skipTimes,
        ];
    }
}
