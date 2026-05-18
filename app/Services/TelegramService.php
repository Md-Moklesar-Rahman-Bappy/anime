<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class TelegramService
{
    protected string $token;

    protected string $apiBase;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token', '');
        $this->apiBase = "https://api.telegram.org/bot{$this->token}";
    }

    public function resolveMessage(string $url): ?array
    {
        try {
            $parsed = $this->parseTmeUrl($url);
            if (! $parsed || ! isset($parsed['message_id'])) {
                return null;
            }

            // Use Python streamer to get message info for the preview
            $streamService = new TelegramStreamService();
            $info = $streamService->getMessageInfo($parsed['message_id']);

            if (! $info) {
                return null;
            }

            // For large files, set needs_streaming flag and construct the proxy URL
            $needsStreaming = ($info['file_size'] ?? 0) > 20 * 1024 * 1024; // > 20MB

            return [
                'file_id' => null, // Not available from Python streamer in preview context
                'file_unique_id' => null,
                'direct_url' => $needsStreaming ? null : null, // No direct URL for large files
                'file_path' => null,
                'file_size' => $info['file_size'] ?? null,
                'duration' => $info['duration'] ?? null,
                'width' => $info['width'] ?? null,
                'height' => $info['height'] ?? null,
                'thumbnail' => null,
                'mime_type' => $info['mime_type'] ?? 'video/mp4',
                'caption' => $info['caption'] ?? null,
                'message_id' => $parsed['message_id'],
                'date' => null,
                'type' => 'mp4',
                'needs_streaming' => $needsStreaming,
            ];
        } catch (Exception $e) {
            Log::warning("Telegram resolveMessage failed: {$e->getMessage()}");
            return null;
        }
    }

    public function resolveFileId(string $fileId): ?array
    {
        try {
            $response = Http::post("{$this->apiBase}/getFile", [
                'file_id' => $fileId,
            ]);

            if (! $response->successful() || ! ($response['ok'] ?? false)) {
                return null;
            }

            $filePath = $response['result']['file_path'] ?? null;
            if (! $filePath) {
                return null;
            }

            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            return [
                'file_id' => $fileId,
                'file_unique_id' => $response['result']['file_unique_id'] ?? null,
                'file_path' => $filePath,
                'file_size' => $response['result']['file_size'] ?? null,
                'direct_url' => "{$this->apiBase}/{$filePath}",
                'extension' => $ext,
                'type' => in_array($ext, ['mp4', 'webm', 'mkv', 'm3u8']) ? $ext : 'mp4',
            ];
        } catch (Exception $e) {
            Log::warning("Telegram resolveFileId failed: {$e->getMessage()}");
            return null;
        }
    }

    public function getChannelUpdates(int $offset = 0, int $limit = 100): array
    {
        try {
            $response = Http::post("{$this->apiBase}/getUpdates", [
                'offset' => $offset,
                'limit' => $limit,
                'allowed_updates' => ['channel_post'],
            ]);

            if (! $response->successful() || ! ($response['ok'] ?? false)) {
                return [];
            }

            return $response['result'] ?? [];
        } catch (Exception $e) {
            Log::warning("Telegram getChannelUpdates failed: {$e->getMessage()}");
            return [];
        }
    }

    public function extractVideoFromUpdate(array $update): ?array
    {
        $post = $update['channel_post'] ?? null;
        if (! $post) {
            return null;
        }

        return $this->extractVideo($post);
    }

    protected function parseTmeUrl(string $url): ?array
    {
        $patterns = [
            '/t\.me\/([a-zA-Z0-9_]+)\/(\d+)/',
            '/telegram\.me\/([a-zA-Z0-9_]+)\/(\d+)/',
            '/t\.me\/c\/(\d+)\/(\d+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $m)) {
                return [
                    'username' => $m[1],
                    'chat_id' => ctype_digit($m[1]) ? (int) $m[1] : '@'.$m[1],
                    'message_id' => (int) $m[2],
                ];
            }
        }

        if (preg_match('/^([a-zA-Z0-9_-]+)$/', $url, $m)) {
            return [
                'username' => $m[1],
                'chat_id' => $m[1],
                'message_id' => null,
                'is_file_id' => true,
            ];
        }

        return null;
    }

    protected function extractVideo(array $message): ?array
    {
        $media = $message['video'] ?? $message['document'] ?? null;
        if (! $media) {
            return null;
        }

        $fileId = $media['file_id'] ?? null;
        if (! $fileId) {
            return null;
        }

        // Try to get direct URL via Bot API first (faster for small files)
        $fileInfo = $this->resolveFileId($fileId);
        $needsStreaming = ! $fileInfo;

        return [
            'file_id' => $fileId,
            'file_unique_id' => $media['file_unique_id'] ?? null,
            'direct_url' => $fileInfo['direct_url'] ?? null,
            'file_path' => $fileInfo['file_path'] ?? null,
            'file_size' => $media['file_size'] ?? null,
            'duration' => $media['duration'] ?? null,
            'width' => $media['width'] ?? null,
            'height' => $media['height'] ?? null,
            'thumbnail' => null,
            'mime_type' => $media['mime_type'] ?? null,
            'caption' => $message['caption'] ?? $message['text'] ?? null,
            'message_id' => $message['message_id'] ?? null,
            'date' => $message['date'] ?? null,
            'type' => 'mp4',
            'needs_streaming' => $needsStreaming,
        ];
    }
}

