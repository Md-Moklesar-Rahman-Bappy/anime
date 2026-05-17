<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class YouTubeService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.youtube.key', '');
    }

    public function getVideoInfo(string $url): ?array
    {
        $videoId = $this->extractVideoId($url);
        if (! $videoId) {
            return null;
        }

        $info = $this->viaOEmbed($videoId);
        if (! $info) {
            return null;
        }

        return $info;
    }

    public function extractVideoId(string $url): ?string
    {
        $patterns = [
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
            '/youtu\.be\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/shorts\/([a-zA-Z0-9_-]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    public function viaOEmbed(string $videoId): ?array
    {
        $url = "https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v={$videoId}&format=json";
        $response = Http::get($url);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        $duration = null;
        if ($this->apiKey) {
            $duration = $this->fetchDuration($videoId);
        }

        return [
            'id' => $videoId,
            'title' => $data['title'] ?? null,
            'author' => $data['author_name'] ?? null,
            'author_url' => $data['author_url'] ?? null,
            'thumbnail' => $data['thumbnail_url'] ?? null,
            'duration' => $duration,
            'embed_url' => "https://www.youtube.com/embed/{$videoId}",
            'watch_url' => "https://www.youtube.com/watch?v={$videoId}",
        ];
    }

    protected function fetchDuration(string $videoId): ?int
    {
        if (! $this->apiKey) {
            return null;
        }

        $url = "https://www.googleapis.com/youtube/v3/videos?id={$videoId}&part=contentDetails&key={$this->apiKey}";
        $response = Http::get($url);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        $duration = $data['items'][0]['contentDetails']['duration'] ?? null;

        if (! $duration) {
            return null;
        }

        return $this->iso8601ToSeconds($duration);
    }

    protected function iso8601ToSeconds(string $iso): int
    {
        $interval = new \DateInterval($iso);

        return ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
    }
}
