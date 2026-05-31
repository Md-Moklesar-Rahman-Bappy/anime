<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Comment;
use App\Models\Favorite;
use App\Services\RelatedContentService;
use App\Services\ViewCounterService;
use App\Services\YouTubeService;
use Illuminate\Support\Facades\Storage;

class WatchController extends Controller
{
    public function __construct(
        protected ViewCounterService $viewCounter,
        protected RelatedContentService $relatedContent,
        protected YouTubeService $youtube,
    ) {}

    public function __invoke($slug)
    {
        $anime = $this->loadAnime($slug);
        $this->viewCounter->increment($anime, 'anime');

        $episode = $this->resolveEpisode($anime);
        $episode->load(['servers', 'skipTimes']);

        $data = [
            'anime' => $anime,
            'episode' => $episode,
            'prevEpisode' => $this->prevEpisode($anime, $episode),
            'nextEpisode' => $this->nextEpisode($anime, $episode),
            'comments' => $this->loadComments($episode),
            'related' => $this->relatedContent->byGenres($anime, $anime->genres, 'genres'),
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
        return Anime::where('slug', $slug)->with(['episodes' => fn($q) => $q->orderBy('number'), 'genres'])->firstOrFail();
    }

    protected function resolveEpisode(Anime $anime)
    {
        $episode = request('ep')
            ? $anime->episodes->firstWhere('number', (int) request('ep'))
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

    protected function resolveUrl(string $url): string
    {
        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            return $url;
        }

        return route('stream.proxy', ['url' => base64_encode($url)]);
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
            $ytVideoId = $this->youtube->extractVideoId($episode->video_path);
            $ytInVideoPath = $ytVideoId !== null;
        }

        if ($youtubeServer) {
            $allServers[] = $this->serverEntry('youtube', 'YouTube', $youtubeServer->url, 'youtube', $youtubeServer->language);
        } elseif ($ytInVideoPath) {
            $allServers[] = $this->serverEntry('youtube', 'YouTube', "https://www.youtube.com/watch?v={$ytVideoId}", 'youtube', 'english');
        }

        $idx = 0;
        foreach ($videoServers as $s) {
            $idx++;
            $allServers[] = $this->serverEntry(
                "video_{$s->id}",
                $s->label ?? "Server {$idx}",
                $this->resolveUrl($s->url),
                $s->type,
                $s->language
            );
        }

        if (! $hasServers && $hasVideoPath && ! $ytInVideoPath) {
            $videoSrc = str_starts_with($episode->video_path, 'http')
                ? $this->resolveUrl($episode->video_path)
                : Storage::url($episode->video_path);
            $allServers[] = $this->serverEntry('local', 'Default', $videoSrc, 'mp4', 'english');
        }

        $skipTimes = $episode->skipTimes->first() ?: null;

        $youtubeVideoId = null;
        if ($youtubeServer) {
            $youtubeVideoId = $this->youtube->extractVideoId($youtubeServer->url);
        } elseif ($ytInVideoPath) {
            $youtubeVideoId = $ytVideoId;
        }

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
            'youtubeVideoId' => $youtubeVideoId,
            'skipTimes' => $skipTimes,
        ];
    }

    protected function serverEntry(string $id, string $label, string $url, string $type, ?string $language): array
    {
        return [
            'server_id' => $id,
            'label' => $label,
            'url' => $url,
            'type' => $type,
            'language' => $language,
        ];
    }
}
