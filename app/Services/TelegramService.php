<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $token;
    protected string $apiBase;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token');

        if (empty($this->token)) {
            throw new Exception('Telegram bot token missing');
        }

        $this->apiBase = "https://api.telegram.org/bot{$this->token}";
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Message (via URL)
    |--------------------------------------------------------------------------
    */

    public function resolveMessage(string $url): ?array
    {
        return Cache::remember("telegram_msg_" . md5($url), 600, function () use ($url) {

            try {
                $parsed = $this->parseTmeUrl($url);

                if (!$parsed || empty($parsed['message_id'])) {
                    return null;
                }

                $streamService = new TelegramStreamService;

                $info = $streamService->getMessageInfo($parsed['message_id']);

                if (!$info) {
                    return null;
                }

                return $this->buildResponse([
                    'message_id' => $parsed['message_id'],
                    'file_size' => $info['file_size'] ?? null,
                    'duration' => $info['duration'] ?? null,
                    'width' => $info['width'] ?? null,
                    'height' => $info['height'] ?? null,
                    'mime_type' => $info['mime_type'] ?? 'video/mp4',
                    'needs_streaming' => true,
                ]);

            } catch (Exception $e) {
                Log::warning("Telegram resolveMessage failed", [
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]);

                return null;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve File ID via Bot API
    |--------------------------------------------------------------------------
    */

    public function resolveFileId(string $fileId): ?array
    {
        return Cache::remember("telegram_file_" . $fileId, 600, function () use ($fileId) {

            try {
                $response = Http::timeout(10)
                    ->retry(3, 300)
                    ->post("{$this->apiBase}/getFile", [
                        'file_id' => $fileId,
                    ]);

                if (!$response->successful() || !($response['ok'] ?? false)) {
                    return null;
                }

                $filePath = $response['result']['file_path'] ?? null;

                if (!$filePath) {
                    return null;
                }

                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

                return $this->buildResponse([
                    'file_id' => $fileId,
                    'file_path' => $filePath,
                    'direct_url' => "https://api.telegram.org/file/bot{$this->token}/{$filePath}",
                    'file_size' => $response['result']['file_size'] ?? null,
                    'mime_type' => $this->normalizeMime($ext),
                    'type' => $ext ?: 'mp4',
                    'needs_streaming' => false,
                ]);

            } catch (Exception $e) {
                Log::warning("Telegram resolveFileId failed", [
                    'file_id' => $fileId,
                    'error' => $e->getMessage(),
                ]);

                return null;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function buildResponse(array $data): array
    {
        return [
            'file_id' => $data['file_id'] ?? null,
            'file_path' => $data['file_path'] ?? null,
            'direct_url' => $data['direct_url'] ?? null,
            'file_size' => $data['file_size'] ?? null,
            'duration' => $data['duration'] ?? null,
            'width' => $data['width'] ?? null,
            'height' => $data['height'] ?? null,
            'mime_type' => $data['mime_type'] ?? 'video/mp4',
            'type' => $data['type'] ?? 'mp4',
            'message_id' => $data['message_id'] ?? null,
            'caption' => $data['caption'] ?? null,
            'needs_streaming' => $data['needs_streaming'] ?? false,
        ];
    }

    protected function normalizeMime(string $ext): string
    {
        return match ($ext) {
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            default => 'video/mp4',
        };
    }

    protected function parseTmeUrl(string $url): ?array
    {
        if (preg_match('/t\.me\/([a-zA-Z0-9_]+)\/(\d+)/', $url, $m)) {
            return [
                'chat_id' => '@' . $m[1],
                'message_id' => (int)$m[2],
            ];
        }

        return null;
    }
}