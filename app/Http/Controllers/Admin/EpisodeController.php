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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EpisodeController extends Controller
{
    protected const LANGUAGES = [
        'english',
        'japanese',
        'hindi',
    ];

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
            $data['created_by'] = auth()->id();
            $data['source_type'] = $this->normalizeSourceType($data['source_type'] ?? 'upload');

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

            Log::error('Episode create failed', [
                'anime_id' => $anime->id,
                'error' => $e->getMessage(),
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
                $data['source_type'] ?? $episode->source_type ?? 'upload'
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

            Log::error('Episode update failed', [
                'anime_id' => $anime->id,
                'episode_id' => $episode->id,
                'error' => $e->getMessage(),
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
            Log::error('Episode delete failed', [
                'anime_id' => $anime->id,
                'episode_id' => $episode->id,
                'error' => $e->getMessage(),
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
                    'storage_disk' => 'local',
                    'source_url' => null,
                    'source_id' => null,
                ]);

                $episode->servers()->delete();
            });

            return back()->with('success', 'Video deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Episode video delete failed', [
                'anime_id' => $anime->id,
                'episode_id' => $episode->id,
                'error' => $e->getMessage(),
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
                ->store("anime/{$anime->slug}/episodes", 'public');

            $data['thumbnail'] = $thumbnailPath;
            $uploadedFiles[] = $thumbnailPath;
        }

        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')
                ->store("anime/{$anime->slug}/videos", 'public');

            $data['video_path'] = $videoPath;
            $data['storage_disk'] = 'local';
            $data['source_type'] = 'upload';

            $uploadedFiles[] = $videoPath;
        }

        if (!$request->hasFile('video') && $request->filled('uploaded_video_path')) {
            $data['video_path'] = $request->input('uploaded_video_path');
            $data['storage_disk'] = 'local';
            $data['source_type'] = 'upload';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Source Metadata Handler
    |--------------------------------------------------------------------------
    */
    protected function enrichWithSourceMetadata(Request $request, array &$data): void
    {
        $sourceType = $this->normalizeSourceType($data['source_type'] ?? 'upload');

        $data['source_type'] = $sourceType;

        if ($sourceType === 'youtube') {
            $this->handleYouTubeSource($request, $data);
            return;
        }

        if ($sourceType === 'telegram') {
            $this->handleTelegramSource($request, $data);
            return;
        }

        if ($sourceType === 'external') {
            $this->handleExternalSource($data);
            return;
        }

        if ($sourceType === 'upload') {
            $data['storage_disk'] = $data['storage_disk'] ?? 'local';
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
        $data['storage_disk'] = 'streaming';

        if (empty($data['duration']) && isset($info['duration'])) {
            $data['duration'] = (int) round(((int) $info['duration']) / 60);
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
        if (!$request->filled('telegram_direct_url')) {
            return;
        }

        $url = $request->input('telegram_direct_url');

        $data['video_path'] = $url;
        $data['source_url'] = $url;
        $data['source_id'] = $request->input('telegram_file_id');
        $data['storage_disk'] = 'streaming';

        if (empty($data['duration']) && $request->filled('telegram_duration')) {
            $data['duration'] = (int) round(((int) $request->input('telegram_duration')) / 60);
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
        $data['storage_disk'] = 'streaming';
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    protected function normalizeSourceType(?string $sourceType): string
    {
        $sourceType = $sourceType ?: 'upload';

        return match ($sourceType) {
            'direct_url' => 'external',
            default => $sourceType,
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

    protected function deleteLocalVideo(Episode $episode): void
    {
        if (
            $episode->video_path &&
            $episode->storage_disk === 'local' &&
            $this->isLocalStoragePath($episode->video_path)
        ) {
            Storage::disk('public')->delete($episode->video_path);
        }
    }

    protected function deleteLocalThumbnail(Episode $episode): void
    {
        if (
            $episode->thumbnail &&
            $this->isLocalStoragePath($episode->thumbnail)
        ) {
            Storage::disk('public')->delete($episode->thumbnail);
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
            $oldStorageDisk === 'local' &&
            $this->isLocalStoragePath($oldVideoPath)
        ) {
            Storage::disk('public')->delete($oldVideoPath);
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
            Storage::disk('public')->delete($oldThumbnail);
        }
    }

    protected function deleteUploadedFiles(array $paths): void
    {
        foreach ($paths as $path) {
            if ($this->isLocalStoragePath($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
