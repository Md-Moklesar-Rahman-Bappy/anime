<?php

namespace App\Services;

use App\Models\Episode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $servers = $episode->servers ?? collect();

        $videoServers = $servers
            ->where('type', '!=', 'youtube')
            ->sortByDesc('priority')
            ->values();

        $youtubeServer = $servers->firstWhere('type', 'youtube');

        $allServers = [];
        $ytVideoId = null;

        /*
        |--------------------------------------------------------------------------
        | YouTube handling
        |--------------------------------------------------------------------------
        */

        if ($youtubeServer) {
            $ytVideoId = $this->youtube->extractVideoId($youtubeServer->url);

            $allServers[] = $this->entry(
                'youtube',
                'YouTube',
                $youtubeServer->url,
                'youtube',
                $this->normalizeLanguage($youtubeServer->language)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Video servers
        |--------------------------------------------------------------------------
        */

        foreach ($videoServers as $index => $server) {

            $type = $server->type;
            $language = $this->normalizeLanguage($server->language);

            $url = $this->shouldProxy($type)
                ? $this->proxy($server->url)
                : $server->url;

            $allServers[] = $this->entry(
                "video_{$server->id}",
                $server->label ?? "Server " . ($index + 1),
                $url,
                $type,
                $language
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback (video_path)
        |--------------------------------------------------------------------------
        */

        if ($videoServers->isEmpty() && $episode->video_path) {

            $url = str_starts_with($episode->video_path, 'http')
                ? $this->proxy($episode->video_path)
                : Storage::url($episode->video_path);

            $allServers[] = $this->entry(
                'default',
                'Default',
                $url,
                'mp4',
                'sub'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Language grouping
        |--------------------------------------------------------------------------
        */

        $groups = collect($allServers)->groupBy('language');

        /*
        |--------------------------------------------------------------------------
        | Select best initial server
        |--------------------------------------------------------------------------
        */

        $initial = collect($allServers)->sortByDesc(function ($s) {
            return match ($s['type']) {
                'm3u8' => 3,
                'mp4' => 2,
                'embed' => 1,
                default => 0,
            };
        })->first();

        return [
            'allServers' => $allServers,
            'languageGroups' => $groups,
            'languages' => $groups->keys()->values()->toArray(),
            'initialServer' => $initial,
            'isYoutubeInit' => $initial['type'] === 'youtube',
            'youtubeVideoId' => $ytVideoId,
            'skipTimes' => $episode->skipTimes->first(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function shouldProxy(string $type): bool
    {
        return !in_array($type, ['youtube']);
    }

    private function proxy(string $url): string
    {
        return route('stream.proxy', ['url' => base64_encode($url)]);
    }

    private function normalizeLanguage(?string $language): string
    {
        return match (strtolower(trim($language ?? ''))) {
            'dub', 'dubbed' => 'dub',
            default => 'sub',
        };
    }

    private function entry(string $id, string $label, string $url, string $type, string $language): array
    {
        return [
            'server_id' => $id,
            'label' => $label,
            'url' => $url,
            'type' => $type,
            'language' => $language,
            'mime' => self::MIME_MAP[$type] ?? null,
        ];
    }
}