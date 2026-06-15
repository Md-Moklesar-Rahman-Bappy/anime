<?php

namespace App\Services;

use App\Models\Episode;
use App\Models\Server;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServerBuilderService
{
    public function createForSource(Episode $episode, array $data): void
    {
        if (empty($data['video_path']) && empty($data['source_url'])) {
            return;
        }

        $sourceType = strtolower($data['source_type'] ?? 'upload');
        $url = $data['video_path'] ?? $data['source_url'];
        $language = $this->normalizeLanguage($data['language'] ?? 'sub');

        try {
            match ($sourceType) {
                'youtube' => $this->createYouTubeServer($episode, $url, $language),
                'upload' => $this->createUploadServer($episode, $data['video_path'] ?? null, $language),
                'external' => $this->createExternalServer($episode, $url, $data['source_label'] ?? null, $language),
                'telegram' => $this->createTelegramServer($episode, $url, $language),
                default => null,
            };
        } catch (\Throwable $e) {
            Log::error('Server creation failed', [
                'episode_id' => $episode->id,
                'source_type' => $sourceType,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function createYouTubeServer(Episode $episode, string $url, string $language): void
    {
        $this->createIfNotExists($episode, $url, 'youtube', 'YouTube', $language);
    }

    protected function createUploadServer(Episode $episode, ?string $videoPath, string $language): void
    {
        if (!$videoPath) {
            return;
        }

        $ext = strtolower(pathinfo($videoPath, PATHINFO_EXTENSION));
        $type = in_array($ext, ['mp4', 'webm', 'mkv']) ? $ext : 'mp4';

        $url = Storage::url($videoPath);

        $this->createIfNotExists($episode, $url, $type, 'Upload', $language);
    }

    protected function createExternalServer(Episode $episode, string $url, ?string $label, string $language): void
    {
        if (!$url) {
            return;
        }

        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

        $type = match ($ext) {
            'm3u8' => 'm3u8',
            'mp4', 'webm', 'mkv' => $ext,
            default => 'embed',
        };

        $this->createIfNotExists(
            $episode,
            $url,
            $type,
            $label ?? 'Server',
            $language
        );
    }

    protected function createTelegramServer(Episode $episode, string $url, string $language): void
    {
        $this->createIfNotExists($episode, $url, 'mp4', 'Telegram', $language);
    }

    /*
    |--------------------------------------------------------------------------
    | Core Safe Creator
    |--------------------------------------------------------------------------
    */

    protected function createIfNotExists(
        Episode $episode,
        string $url,
        string $type,
        string $label,
        string $language
    ): void {

        $exists = Server::where('episode_id', $episode->id)
            ->where('url', $url)
            ->exists();

        if ($exists) {
            return;
        }

        Server::create([
            'episode_id' => $episode->id,
            'label' => $label,
            'url' => $url,
            'type' => $type,
            'language' => $language,
            'priority' => 0,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function normalizeLanguage(string $language): string
    {
        $lang = strtolower(trim($language));

        return match ($lang) {
            'english', 'sub', 'subtitle' => 'sub',
            'dub', 'dubbed' => 'dub',
            default => 'sub',
        };
    }
}