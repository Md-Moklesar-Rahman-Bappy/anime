<?php

namespace App\Services;

use App\Models\Episode;
use App\Models\Server;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServerBuilderService
{
    /*
    |--------------------------------------------------------------------------
    | MAIN ENTRY
    |--------------------------------------------------------------------------
    */
    public function createForSource(Episode $episode, array $data): void
    {
        $sourceType = strtolower(trim($data['source_type'] ?? 'upload'));
        $language = $this->normalizeLanguage($data['language'] ?? 'sub');

        $url = $this->resolveUrl($data);

        if (!$url) {
            return;
        }

        try {
            DB::transaction(function () use ($episode, $data, $sourceType, $url, $language) {

                match ($sourceType) {
                    'youtube' => $this->createYouTubeServer($episode, $url, $language),
                    'upload' => $this->createUploadServer($episode, $data['video_path'] ?? null, $language),
                    'external' => $this->createExternalServer($episode, $url, $data['source_label'] ?? null, $language),
                    'telegram' => $this->createTelegramServer($episode, $url, $language),
                    default => null,
                };
            });
        } catch (\Throwable $e) {
            logger()->error('Server creation failed', [
                'episode_id' => $episode->id,
                'source_type' => $sourceType,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SERVER TYPES
    |--------------------------------------------------------------------------
    */

    protected function createYouTubeServer(Episode $episode, string $url, string $language): void
    {
        $this->createIfNotExists($episode, $url, 'youtube', 'YouTube', $language);
    }

    protected function createUploadServer(Episode $episode, ?string $videoPath, string $language): void
    {
        if (!$videoPath) return;

        $ext = strtolower(pathinfo($videoPath, PATHINFO_EXTENSION));

        $type = in_array($ext, ['mp4', 'webm', 'mkv'], true) ? $ext : 'mp4';

        $url = Storage::url($videoPath);

        $this->createIfNotExists($episode, $url, $type, 'Upload', $language);
    }

    protected function createExternalServer(Episode $episode, string $url, ?string $label, string $language): void
    {
        $path = parse_url($url, PHP_URL_PATH);

        $ext = strtolower(pathinfo($path ?? '', PATHINFO_EXTENSION));

        $type = match ($ext) {
            'm3u8' => 'm3u8',
            'mp4', 'webm', 'mkv' => $ext,
            default => 'embed',
        };

        $this->createIfNotExists(
            $episode,
            $url,
            $type,
            $label ?: 'Server',
            $language
        );
    }

    protected function createTelegramServer(Episode $episode, string $url, string $language): void
    {
        $this->createIfNotExists($episode, $url, 'mp4', 'Telegram', $language);
    }

    /*
    |--------------------------------------------------------------------------
    | SAFE CREATOR (CRITICAL)
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
            ->where('type', $type)
            ->where('language', $language)
            ->lockForUpdate()
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
            'priority' => $this->resolvePriority($type),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    protected function resolveUrl(array $data): ?string
    {
        return $data['video_path']
            ?? $data['source_url']
            ?? null;
    }

    protected function normalizeLanguage(string $language): string
    {
        $lang = strtolower(trim($language));

        return match ($lang) {
            'english', 'sub', 'subtitle' => 'sub',
            'dub', 'dubbed' => 'dub',
            default => 'sub',
        };
    }

    protected function resolvePriority(string $type): int
    {
        return match ($type) {
            'm3u8' => 1,
            'mp4' => 2,
            'youtube' => 3,
            default => 5,
        };
    }
}
