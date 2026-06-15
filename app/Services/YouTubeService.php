<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class YouTubeService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.youtube.key', '');
    }

    /*
    |--------------------------------------------------------------------------
    | Main Entry
    |--------------------------------------------------------------------------
    */

    public function getVideoInfo(string $url): ?array
    {
        $videoId = $this->extractVideoId($url);

        if (!$videoId) {
            return null;
        }

        return Cache::remember("youtube_{$videoId}", 3600, function () use ($videoId) {

            // ✅ Try oEmbed first
            $info = $this->viaOEmbed($videoId);

            // ✅ fallback to API if needed
            if (!$info && $this->apiKey) {
                return $this->viaApi($videoId);
            }

            return $info;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Extract Video ID
    |--------------------------------------------------------------------------
    */

    public function extractVideoId(string $url): ?string
    {
        $patterns = [
            '/v=([a-zA-Z0-9_-]{11})/',
            '/youtu\.be\/([a-zA-Z0-9_-]{11})/',
            '/embed\/([a-zA-Z0-9_-]{11})/',
            '/shorts\/([a-zA-Z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | oEmbed
    |--------------------------------------------------------------------------
    */

    protected function viaOEmbed(string $videoId): ?array
    {
        try {
            $url = "https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v={$videoId}&format=json";

            $response = Http::timeout(5)
                ->retry(2, 200)
                ->get($url);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            return [
                'id' => $videoId,
                'title' => $data['title'] ?? null,
                'author' => $data['author_name'] ?? null,
                'thumbnail' => $data['thumbnail_url'] ?? null,
                'duration' => $this->apiKey ? $this->fetchDuration($videoId) : null,
                'embed_url' => "https://www.youtube.com/embed/{$videoId}",
                'watch_url' => "https://www.youtube.com/watch?v={$videoId}",
            ];

        } catch (\Throwable) {
            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Full API fallback
    |--------------------------------------------------------------------------
    */

    protected function viaApi(string $videoId): ?array
    {
        try {
            $url = "https://www.googleapis.com/youtube/v3/videos";

            $response = Http::timeout(5)
                ->retry(2, 200)
                ->get($url, [
                    'id' => $videoId,
                    'part' => 'snippet,contentDetails',
                    'key' => $this->apiKey,
                ]);

            if (!$response->successful()) {
                return null;
            }

            $item = $response['items'][0] ?? null;

            if (!$item) {
                return null;
            }

            return [
                'id' => $videoId,
                'title' => $item['snippet']['title'] ?? null,
                'author' => $item['snippet']['channelTitle'] ?? null,
                'thumbnail' => $item['snippet']['thumbnails']['high']['url'] ?? null,
                'duration' => $this->iso8601ToSeconds($item['contentDetails']['duration'] ?? null),
                'embed_url' => "https://www.youtube.com/embed/{$videoId}",
                'watch_url' => "https://www.youtube.com/watch?v={$videoId}",
            ];

        } catch (\Throwable) {
            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Duration Parser
    |--------------------------------------------------------------------------
    */

    protected function iso8601ToSeconds(?string $iso): ?int
    {
        if (!$iso) {
            return null;
        }

        try {
            $interval = new \DateInterval($iso);

            return ($interval->h * 3600)
                + ($interval->i * 60)
                + $interval->s;

        } catch (\Throwable) {
            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Duration via API
    |--------------------------------------------------------------------------
    */

    protected function fetchDuration(string $videoId): ?int
    {
        return $this->viaApi($videoId)['duration'] ?? null;
    }
}