<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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
        $parsed = $this->parseTmeUrl($url);
        if (! $parsed) {
            return null;
        }

        $message = $this->fetchMessage($parsed['chat_id'], $parsed['message_id']);
        if (! $message) {
            return null;
        }

        return $this->extractVideo($message);
    }

    public function resolveFileId(string $fileId): ?array
    {
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
    }

    public function getChannelUpdates(int $offset = 0, int $limit = 100): array
    {
        $response = Http::post("{$this->apiBase}/getUpdates", [
            'offset' => $offset,
            'limit' => $limit,
            'allowed_updates' => ['channel_post'],
        ]);

        if (! $response->successful() || ! ($response['ok'] ?? false)) {
            return [];
        }

        return $response['result'] ?? [];
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

    protected function fetchMessage(string $chatId, int $messageId): ?array
    {
        $response = Http::post("{$this->apiBase}/forwardMessage", [
            'chat_id' => $chatId,
            'from_chat_id' => $chatId,
            'message_id' => $messageId,
        ]);

        if ($response->successful()) {
            $result = $response['result'] ?? null;
            if ($result) {
                return $result;
            }
        }

        $response = Http::post("{$this->apiBase}/getChat", [
            'chat_id' => $chatId,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $response = Http::post("{$this->apiBase}/getUpdates", [
            'allowed_updates' => ['channel_post'],
            'limit' => 100,
        ]);

        if (! $response->successful() || ! ($response['ok'] ?? false)) {
            return null;
        }

        foreach ($response['result'] as $update) {
            $post = $update['channel_post'] ?? null;
            if ($post && ($post['message_id'] ?? null) === $messageId) {
                return $post;
            }
        }

        return null;
    }

    protected function extractVideo(array $message): ?array
    {
        $video = $message['video'] ?? $message['document'] ?? null;
        if (! $video) {
            return null;
        }

        $fileId = $video['file_id'];

        $fileInfo = $this->resolveFileId($fileId);

        $thumb = null;
        if (! empty($video['thumbnail'])) {
            $thumbInfo = $this->resolveFileId($video['thumbnail']['file_id']);
            $thumb = $thumbInfo['direct_url'] ?? null;
        }

        $result = [
            'file_id' => $fileId,
            'file_unique_id' => $video['file_unique_id'] ?? null,
            'direct_url' => null,
            'file_size' => $video['file_size'] ?? null,
            'duration' => $video['duration'] ?? null,
            'width' => $video['width'] ?? null,
            'height' => $video['height'] ?? null,
            'thumbnail' => $thumb,
            'mime_type' => $video['mime_type'] ?? null,
            'caption' => $message['caption'] ?? null,
            'message_id' => $message['message_id'] ?? null,
            'date' => $message['date'] ?? null,
            'type' => 'mp4',
            'needs_streaming' => false,
        ];

        if ($fileInfo) {
            $result['direct_url'] = $fileInfo['direct_url'];
            $result['file_path'] = $fileInfo['file_path'];
            $result['type'] = $fileInfo['type'] ?? 'mp4';
            $result['file_size'] = $fileInfo['file_size'] ?? $result['file_size'];
        } else {
            $result['needs_streaming'] = true;
        }

        return $result;
    }
}
