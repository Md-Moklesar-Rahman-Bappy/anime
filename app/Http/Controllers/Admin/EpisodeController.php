<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEpisodeRequest;
use App\Models\Anime;
use App\Models\Episode;
use App\Services\ServerBuilderService;
use App\Services\YouTubeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EpisodeController extends Controller
{
    protected const LANGUAGES = [
        'english',
        'japanese',
        'hindi',
    ];

    protected const SOURCE_UPLOAD = 'upload';
    protected const SOURCE_YOUTUBE = 'youtube';
    protected const SOURCE_TELEGRAM = 'telegram';
    protected const SOURCE_EXTERNAL = 'external';

    protected const STREAMING_DISK = 'streaming';
    protected const PUBLIC_DISK = 'public';

    public function __construct(
        protected YouTubeService $youtube,
        protected ServerBuilderService $serverBuilder,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Episodes List
    |--------------------------------------------------------------------------
    */

    public function index(Anime $anime)
    {
        $episodes = $anime->episodes()
            ->select(
                'id',
                'anime_id',
                'number',
                'title',
                'thumbnail',
                'source_type',
                'has_sub',
                'has_dub',
                'created_at'
            )
            ->orderBy('number')
            ->paginate(20)
            ->withQueryString();

        return view('admin.episodes.index', compact('anime', 'episodes'));
    }

    /*
    |--------------------------------------------------------------------------
    | Redirect Show To Edit
    |--------------------------------------------------------------------------
    */

    public function show(Anime $anime, Episode $episode)
    {
        $this->ensureEpisodeBelongsToAnime($anime, $episode);

        return redirect()->route('admin.anime.episodes.edit', [$anime, $episode]);
    }

    /*
    |--------------------------------------------------------------------------
    | Create Form
    |--------------------------------------------------------------------------
    */

    public function create(Anime $anime)
    {
        return view('admin.episodes.form', [
            'anime' => $anime,
            'episode' => null,
            'languages' => self::LANGUAGES,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Store Episode
    |--------------------------------------------------------------------------
    */

    public function store(StoreEpisodeRequest $request, Anime $anime)
    {
        $uploadedFiles = [];

        try {
            $data = $request->validated();

            $data['anime_id'] = $anime->id;
            $data['has_sub'] = $request->boolean('has_sub');
            $data['has_dub'] = $request->boolean('has_dub');
            $data['created_by'] = $request->user()?->id;
            $data['source_type'] = $this->normalizeSourceType($data['source_type'] ?? self::SOURCE_UPLOAD);

            $this->handleFileUploads($request, $anime, $data, $uploadedFiles);
            $this->enrichWithSourceMetadata($request, $data);

            DB::transaction(function () use ($data) {
                $episode = Episode::create($data);

                $this->serverBuilder->createForSource($episode, $data);
            });

            return redirect()
                ->route('admin.anime.episodes.index', $anime)
                ->with('success', 'Episode created successfully.');
        } catch (\Throwable $e) {
            $this->deleteUploadedFiles($uploadedFiles);

            $this->logError('Episode create failed', $e, [
                'anime_id' => $anime->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create episode.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Form
    |--------------------------------------------------------------------------
    */

    public function edit(Anime $anime, Episode $episode)
    {
        $this->ensureEpisodeBelongsToAnime($anime, $episode);

        $episode->load('servers');

        return view('admin.episodes.form', [
            'anime' => $anime,
            'episode' => $episode,
            'languages' => self::LANGUAGES,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Episode
    |--------------------------------------------------------------------------
    */

    public function update(StoreEpisodeRequest $request, Anime $anime, Episode $episode)
    {
        $this->ensureEpisodeBelongsToAnime($anime, $episode);

        $uploadedFiles = [];

        try {
            $data = $request->validated();

            $data['has_sub'] = $request->boolean('has_sub');
            $data['has_dub'] = $request->boolean('has_dub');
            $data['source_type'] = $this->normalizeSourceType(
                $data['source_type'] ?? $episode->source_type ?? self::SOURCE_UPLOAD
            );

            $oldVideoPath = $episode->video_path;
            $oldThumbnail = $episode->thumbnail;
            $oldStorageDisk = $episode->storage_disk;

            $this->handleFileUploads($request, $anime, $data, $uploadedFiles);
            $this->enrichWithSourceMetadata($request, $data);

            DB::transaction(function () use ($episode, $data) {
                $episode->update($data);

                $episode->servers()->delete();

                $this->serverBuilder->createForSource($episode, $data);
            });

            $this->deleteOldVideoIfReplaced($oldVideoPath, $oldStorageDisk, $data);
            $this->deleteOldThumbnailIfReplaced($oldThumbnail, $data);

            return redirect()
                ->route('admin.anime.episodes.index', $anime)
                ->with('success', 'Episode updated successfully.');
        } catch (\Throwable $e) {
            $this->deleteUploadedFiles($uploadedFiles);

            $this->logError('Episode update failed', $e, [
                'anime_id' => $anime->id,
                'episode_id' => $episode->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update episode.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Episode
    |--------------------------------------------------------------------------
    */

    public function destroy(Anime $anime, Episode $episode)
    {
        $this->ensureEpisodeBelongsToAnime($anime, $episode);

        try {
            DB::transaction(function () use ($episode) {
                $this->deleteLocalVideo($episode);
                $this->deleteLocalThumbnail($episode);

                $episode->servers()->delete();
                $episode->delete();
            });

            return redirect()
                ->route('admin.anime.episodes.index', $anime)
                ->with('success', 'Episode deleted successfully.');
        } catch (\Throwable $e) {
            $this->logError('Episode delete failed', $e, [
                'anime_id' => $anime->id,
                'episode_id' => $episode->id,
            ]);

            return back()->with('error', 'Failed to delete episode.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Episode Video Only
    |--------------------------------------------------------------------------
    */

    public function deleteVideo(Anime $anime, Episode $episode)
    {
        $this->ensureEpisodeBelongsToAnime($anime, $episode);

        try {
            DB::transaction(function () use ($episode) {
                $this->deleteLocalVideo($episode);

                $episode->update([
                    'video_path' => null,
                    'storage_disk' => null,
                    'source_url' => null,
                    'source_id' => null,
                    'telegram_message_id' => null,
                    'source_type' => self::SOURCE_UPLOAD,
                ]);

                $episode->servers()->delete();
            });

            return back()->with('success', 'Video deleted successfully.');
        } catch (\Throwable $e) {
            $this->logError('Episode video delete failed', $e, [
                'anime_id' => $anime->id,
                'episode_id' => $episode->id,
            ]);

            return back()->with('error', 'Failed to delete video.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | File Upload Handler
    |--------------------------------------------------------------------------
    */

    protected function handleFileUploads(
        Request $request,
        Anime $anime,
        array &$data,
        array &$uploadedFiles
    ): void {
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')
                ->store("anime/{$anime->slug}/episodes", self::PUBLIC_DISK);

            $data['thumbnail'] = $thumbnailPath;

            $uploadedFiles[] = [
                'disk' => self::PUBLIC_DISK,
                'path' => $thumbnailPath,
            ];
        }

        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')
                ->store("anime/{$anime->slug}/videos", self::PUBLIC_DISK);

            $data['video_path'] = $videoPath;
            $data['storage_disk'] = self::PUBLIC_DISK;
            $data['source_type'] = self::SOURCE_UPLOAD;

            $uploadedFiles[] = [
                'disk' => self::PUBLIC_DISK,
                'path' => $videoPath,
            ];
        }

        if (!$request->hasFile('video') && $request->filled('uploaded_video_path')) {
            $data['video_path'] = $request->input('uploaded_video_path');
            $data['storage_disk'] = self::PUBLIC_DISK;
            $data['source_type'] = self::SOURCE_UPLOAD;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Source Metadata Handler
    |--------------------------------------------------------------------------
    */

    protected function enrichWithSourceMetadata(Request $request, array &$data): void
    {
        $sourceType = $this->normalizeSourceType($data['source_type'] ?? self::SOURCE_UPLOAD);

        $data['source_type'] = $sourceType;

        if ($sourceType === self::SOURCE_YOUTUBE) {
            $this->handleYouTubeSource($request, $data);
            return;
        }

        if ($sourceType === self::SOURCE_TELEGRAM) {
            $this->handleTelegramSource($request, $data);
            return;
        }

        if ($sourceType === self::SOURCE_EXTERNAL) {
            $this->handleExternalSource($data);
            return;
        }

        if ($sourceType === self::SOURCE_UPLOAD) {
            $data['storage_disk'] = $data['storage_disk'] ?? self::PUBLIC_DISK;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | YouTube Source
    |--------------------------------------------------------------------------
    */

    protected function handleYouTubeSource(Request $request, array &$data): void
    {
        $url = $request->input('youtube_url') ?: ($data['source_url'] ?? null);

        if (!$url) {
            return;
        }

        $info = $this->youtube->getVideoInfo($url);

        if (!$info) {
            return;
        }

        $data['source_id'] = $info['id'] ?? null;
        $data['source_url'] = $info['watch_url'] ?? $url;
        $data['video_path'] = $info['embed_url'] ?? null;
        $data['storage_disk'] = self::STREAMING_DISK;

        // ✅ Store duration in seconds
        if (empty($data['duration']) && isset($info['duration'])) {
            $data['duration'] = (int) $info['duration'];
        }

        if (empty($data['thumbnail']) && !empty($info['thumbnail'])) {
            $data['thumbnail'] = $info['thumbnail'];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Telegram Source
    |--------------------------------------------------------------------------
    */

    protected function handleTelegramSource(Request $request, array &$data): void
    {
        $url = $request->input('telegram_direct_url') ?: ($data['source_url'] ?? null);

        if (!$url) {
            return;
        }

        $data['video_path'] = $url;
        $data['source_url'] = $url;
        $data['source_id'] = $request->input('telegram_file_id');
        $data['telegram_message_id'] = $request->input('telegram_message_id');
        $data['storage_disk'] = self::STREAMING_DISK;

        // ✅ Store duration in seconds
        if (empty($data['duration']) && $request->filled('telegram_duration')) {
            $data['duration'] = (int) $request->input('telegram_duration');
        }

        if (empty($data['thumbnail']) && $request->filled('telegram_thumb')) {
            $data['thumbnail'] = $request->input('telegram_thumb');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | External Source
    |--------------------------------------------------------------------------
    */

    protected function handleExternalSource(array &$data): void
    {
        $url = $data['video_path'] ?? $data['source_url'] ?? null;

        if (!$url) {
            return;
        }

        $data['video_path'] = $url;
        $data['source_url'] = $data['source_url'] ?? $url;
        $data['storage_disk'] = self::STREAMING_DISK;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function normalizeSourceType(?string $sourceType): string
    {
        $sourceType = strtolower(trim($sourceType ?: self::SOURCE_UPLOAD));

        return match ($sourceType) {
            'direct_url' => self::SOURCE_EXTERNAL,
            self::SOURCE_UPLOAD,
            self::SOURCE_YOUTUBE,
            self::SOURCE_TELEGRAM,
            self::SOURCE_EXTERNAL => $sourceType,
            default => self::SOURCE_UPLOAD,
        };
    }

    protected function ensureEpisodeBelongsToAnime(Anime $anime, Episode $episode): void
    {
        abort_if((int) $episode->anime_id !== (int) $anime->id, 404);
    }

    protected function isLocalStoragePath(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        return !str_starts_with($path, 'http://') &&
            !str_starts_with($path, 'https://');
    }

    protected function canDeleteFromPublicDisk(?string $path, ?string $disk = null): bool
    {
        if (!$this->isLocalStoragePath($path)) {
            return false;
        }

        // ✅ Backward compatibility: earlier code saved "local" while files were in public disk
        return in_array($disk, [self::PUBLIC_DISK, 'local', null], true);
    }

    protected function deleteLocalVideo(Episode $episode): void
    {
        if ($this->canDeleteFromPublicDisk($episode->video_path, $episode->storage_disk)) {
            Storage::disk(self::PUBLIC_DISK)->delete($episode->video_path);
        }
    }

    protected function deleteLocalThumbnail(Episode $episode): void
    {
        if ($this->isLocalStoragePath($episode->thumbnail)) {
            Storage::disk(self::PUBLIC_DISK)->delete($episode->thumbnail);
        }
    }

    protected function deleteOldVideoIfReplaced(
        ?string $oldVideoPath,
        ?string $oldStorageDisk,
        array $data
    ): void {
        if (
            isset($data['video_path']) &&
            $oldVideoPath &&
            $oldVideoPath !== $data['video_path'] &&
            $this->canDeleteFromPublicDisk($oldVideoPath, $oldStorageDisk)
        ) {
            Storage::disk(self::PUBLIC_DISK)->delete($oldVideoPath);
        }
    }

    protected function deleteOldThumbnailIfReplaced(?string $oldThumbnail, array $data): void
    {
        if (
            isset($data['thumbnail']) &&
            $oldThumbnail &&
            $oldThumbnail !== $data['thumbnail'] &&
            $this->isLocalStoragePath($oldThumbnail)
        ) {
            Storage::disk(self::PUBLIC_DISK)->delete($oldThumbnail);
        }
    }

    protected function deleteUploadedFiles(array $files): void
    {
        foreach ($files as $file) {
            if (is_array($file)) {
                Storage::disk($file['disk'])->delete($file['path']);
                continue;
            }

            if ($this->isLocalStoragePath($file)) {
                Storage::disk(self::PUBLIC_DISK)->delete($file);
            }
        }
    }
}
