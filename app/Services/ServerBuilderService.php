<?php

namespace App\Services;

use App\Models\Episode;
use App\Models\Server;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServerBuilderService
{
    public function createForSource(Episode $episode, array $data): void
    {
        if (empty($data['video_path']) && empty($data['source_url'])) {
            return;
        }

        $sourceType = $data['source_type'] ?? 'upload';
        $url = $data['video_path'] ?? $data['source_url'];
        $language = $data['language'] ?? 'english';

        match ($sourceType) {
            'youtube' => $this->createYouTubeServer($episode, $url, $language),
            'upload' => $this->createUploadServer($episode, $data['video_path'], $language),
            'external' => $this->createExternalServer($episode, $url, $data['source_label'] ?? null, $language),
            'telegram' => $this->createTelegramServer($episode, $url, $language),
            default => null,
        };
    }

    protected function createYouTubeServer(Episode $episode, string $url, string $language): void
    {
        Server::create([
            'episode_id' => $episode->id,
            'label' => 'YouTube',
            'url' => $url,
            'type' => 'youtube',
            'language' => $language,
        ]);
    }

    protected function createUploadServer(Episode $episode, string $videoPath, string $language): void
    {
        $ext = strtolower(pathinfo($videoPath, PATHINFO_EXTENSION));
        $type = in_array($ext, ['mp4', 'webm', 'mkv']) ? $ext : 'mp4';

        Server::create([
            'episode_id' => $episode->id,
            'label' => 'Upload',
            'url' => url('storage/' . $videoPath),
            'type' => $type,
            'language' => $language,
        ]);
    }

    protected function createExternalServer(Episode $episode, string $url, ?string $label, string $language): void
    {
        $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        $type = match ($ext) {
            'm3u8' => 'm3u8',
            'mp4', 'webm', 'mkv' => $ext,
            default => 'embed',
        };

        Server::create([
            'episode_id' => $episode->id,
            'label' => $label ?? 'Server',
            'url' => $url,
            'type' => $type,
            'language' => $language,
        ]);
    }

    protected function createTelegramServer(Episode $episode, string $url, string $language): void
    {
        Server::create([
            'episode_id' => $episode->id,
            'label' => 'Telegram',
            'url' => $url,
            'type' => 'mp4',
            'language' => $language,
        ]);
    }
}
