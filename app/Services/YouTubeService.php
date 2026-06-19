<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class YouTubeService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = (string) config('services.youtube.key', '');
    }

    /*
    |--------------------------------------------------------------------------
    | MAIN ENTRY
    |--------------------------------------------------------------------------
    */
    public function getVideoInfo(string $url): ?array
    {
        $videoId = $this->extractVideoId($url);

        if (!$videoId) {
            return null;
        }

        return Cache::remember("youtube_{$videoId}", 3600, function () use ($videoId) {

            // ✅ Try oEmbed first (fast)
            $info = $this->viaOEmbed($videoId);

            if ($info) {
                return $info;
            }

            // ✅ Fallback to API (if key exists)
            if ($this->apiKey) {
                return $this->viaApi($videoId);
            }

            return null;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | EXTRACT VIDEO ID
    |--------------------------------------------------------------------------
    */
    public function extractVideoId(string $url): ?string
    {
        if (!$url) {
            return null;
        }

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
    | OEMBED (FAST METHOD)
    |--------------------------------------------------------------------------
    */
    protected function viaOEmbed(string $videoId): ?array
    {
        try {
            $url = "https://www.youtube.com/oembed";

            $response = Http::timeout(5)
                ->retry(2, 200)
                ->get($url, [
                    'url' => "https://www.youtube.com/watch?v={$videoId}",
                    'format' => 'json',
                ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            if (!is_array($data)) {
                return null;
            }

            return [
                'id'        => $videoId,
                'title'     => $data['title'] ?? null,
                'author'    => $data['author_name'] ?? null,
                'thumbnail' => $data['thumbnail_url'] ?? null,
                'duration'  => null, // oEmbed does not provide duration
                'embed_url' => "https://www.youtube.com/embed/{$videoId}",
                'watch_url' => "https://www.youtube.com/watch?v={$videoId}",
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | YOUTUBE DATA API (FULL)
    |--------------------------------------------------------------------------
    */
    protected function viaApi(string $videoId): ?array
    {
        try {
            $response = Http::timeout(5)
                ->retry(2, 200)
                ->get('https://www.googleapis.com/youtube/v3/videos', [
                    'id'   => $videoId,
                    'part' => 'snippet,contentDetails',
                    'key'  => $this->apiKey,
                ]);

            if (!$response->successful()) {
                return null;
            }

            $json = $response->json();

            if (!isset($json['items'][0])) {
                return null;
            }

            $item = $json['items'][0];

            return [
                'id'        => $videoId,
                'title'     => $item['snippet']['title'] ?? null,
                'author'    => $item['snippet']['channelTitle'] ?? null,
                'thumbnail' => $item['snippet']['thumbnails']['high']['url']
                    ?? $item['snippet']['thumbnails']['default']['url']
                    ?? null,
                'duration'  => $this->iso8601ToSeconds($item['contentDetails']['duration'] ?? null),
                'embed_url' => "https://www.youtube.com/embed/{$videoId}",
                'watch_url' => "https://www.youtube.com/watch?v={$videoId}",
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ISO 8601 DURATION → SECONDS
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
}
