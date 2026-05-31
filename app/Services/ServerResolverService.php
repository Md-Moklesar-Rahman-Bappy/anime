<?php

namespace App\Services;

use App\Models\Episode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ServerResolverService
{
    private const MIME_MAP = [
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'm3u8' => 'application/x-mpegURL',
    ];

    public function __construct(
        protected YouTubeService $youtube,
    ) {}

    public function resolveAll(Episode $episode): array
    {
        $allServers = [];
        $youtubeServer = $episode->servers->firstWhere('type', 'youtube');
        $videoServers = $episode->servers->where('type', '!=', 'youtube');
        $hasServers = $videoServers->isNotEmpty();
        $hasVideoPath = !empty($episode->video_path);

        $ytVideoId = null;
        if ($hasVideoPath && !$youtubeServer) {
            $ytVideoId = $this->youtube->extractVideoId($episode->video_path);
        }

        if ($youtubeServer) {
            $allServers[] = $this->entry('youtube', 'YouTube', $youtubeServer->url, 'youtube', $youtubeServer->language);
            $ytVideoId = $this->youtube->extractVideoId($youtubeServer->url);
        } elseif ($ytVideoId) {
            $allServers[] = $this->entry('youtube', 'YouTube', "https://www.youtube.com/watch?v={$ytVideoId}", 'youtube', 'english');
        }

        $idx = 0;
        foreach ($videoServers as $s) {
            $idx++;
            $allServers[] = $this->entry(
                "video_{$s->id}",
                $s->label ?? "Server {$idx}",
                $this->resolveUrl($s->url),
                $s->type,
                $s->language
            );
        }

        if (!$hasServers && $hasVideoPath && !$ytVideoId) {
            $videoSrc = str_starts_with($episode->video_path, 'http')
                ? $this->resolveUrl($episode->video_path)
                : Storage::url($episode->video_path);
            $allServers[] = $this->entry('local', 'Default', $videoSrc, 'mp4', 'english');
        }

        $languageGroups = collect($allServers)->groupBy('language');

        return [
            'allServers' => $allServers,
            'languageGroups' => $languageGroups,
            'languages' => $languageGroups->keys()->values()->toArray(),
            'initialServer' => $allServers[0] ?? null,
            'isYoutubeInit' => $allServers[0]['type'] ?? null === 'youtube',
            'youtubeVideoId' => $ytVideoId,
            'skipTimes' => $episode->skipTimes->first(),
        ];
    }

    private function resolveUrl(string $url): string
    {
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            return $url;
        }

        return route('stream.proxy', ['url' => base64_encode($url)]);
    }

    private function entry(string $id, string $label, string $url, string $type, ?string $language): array
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
