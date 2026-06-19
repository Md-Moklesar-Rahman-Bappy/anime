<?php

namespace App\Services;

use App\Models\Episode;
use Illuminate\Support\Facades\Storage;

class ServerResolverService
{
    private const MIME_MAP = [
        'mp4'   => 'video/mp4',
        'webm'  => 'video/webm',
        'm3u8'  => 'application/x-mpegURL',
    ];

    public function __construct(
        protected YouTubeService $youtube,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | RESOLVE ALL SERVERS
    |--------------------------------------------------------------------------
    */
    public function resolveAll(Episode $episode): array
    {
        // ✅ prevent N+1 queries
        $episode->loadMissing(['servers', 'skipTimes']);

        $servers = $episode->servers ?? collect();

        /*
        |--------------------------------------------------------------------------
        | Separate servers
        |--------------------------------------------------------------------------
        */
        $videoServers = $servers
            ->where('type', '!=', 'youtube')
            ->sortBy('priority') // ✅ lowest = best
            ->values();

        $youtubeServer = $servers->firstWhere('type', 'youtube');

        $allServers = [];
        $ytVideoId = null;

        /*
        |--------------------------------------------------------------------------
        | YouTube Server
        |--------------------------------------------------------------------------
        */
        if ($youtubeServer && !empty($youtubeServer->url)) {

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
        | Video Servers
        |--------------------------------------------------------------------------
        */
        foreach ($videoServers as $index => $server) {

            if (empty($server->url)) {
                continue;
            }

            $type = $server->type;
            $language = $this->normalizeLanguage($server->language);

            $url = $this->shouldProxy($server->url, $type)
                ? $this->proxy($server->url)
                : $server->url;

            $allServers[] = $this->entry(
                "video_{$server->id}",
                $server->label ?: "Server " . ($index + 1),
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
        if ($videoServers->isEmpty() && !empty($episode->video_path)) {

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
        | Group by language
        |--------------------------------------------------------------------------
        */
        $groups = collect($allServers)->groupBy('language');

        /*
        |--------------------------------------------------------------------------
        | Select best initial server
        |--------------------------------------------------------------------------
        */
        $initial = collect($allServers)
            ->filter(fn($s) => $s['type'] !== 'youtube') // prefer real video
            ->sortByDesc(fn($s) => match ($s['type']) {
                'm3u8' => 3,
                'mp4'  => 2,
                'embed' => 1,
                default => 0,
            })
            ->first();

        // ✅ fallback to YouTube if no video server
        if (!$initial) {
            $initial = collect($allServers)
                ->first(fn($s) => $s['type'] === 'youtube');
        }

        return [
            'allServers'      => $allServers,
            'languageGroups' => $groups,
            'languages'      => $groups->keys()->values()->toArray(),
            'initialServer'  => $initial,
            'isYoutubeInit'  => ($initial['type'] ?? null) === 'youtube',
            'youtubeVideoId' => $ytVideoId,
            'skipTimes'      => $episode->skipTimes->first(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function shouldProxy(string $url, string $type): bool
    {
        // ✅ don't proxy YouTube
        if ($type === 'youtube') {
            return false;
        }

        // ✅ only proxy external URLs
        return str_starts_with($url, 'http');
    }

    private function proxy(string $url): string
    {
        return route('stream.proxy', [
            'url' => base64_encode($url),
        ]);
    }

    private function normalizeLanguage(?string $language): string
    {
        return match (strtolower(trim((string) $language))) {
            'dub', 'dubbed' => 'dub',
            default => 'sub',
        };
    }

    private function entry(
        string $id,
        string $label,
        string $url,
        string $type,
        string $language
    ): array {
        return [
            'server_id' => $id,
            'label'     => $label,
            'url'       => $url,
            'type'      => $type,
            'language'  => $language,
            'mime'      => self::MIME_MAP[$type] ?? null,
        ];
    }
}
