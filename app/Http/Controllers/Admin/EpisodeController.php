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
    protected const LANGUAGES = ['english', 'japanese', 'hindi'];

    public function __construct(
        protected YouTubeService $youtube,
        protected ServerBuilderService $serverBuilder,
    ) {}

    public function index(Anime $anime)
    {
        return view('admin.episodes.index', [
            'anime' => $anime,
            'episodes' => $anime->episodes()
                ->orderBy('number')
                ->paginate(20),
        ]);
    }

    public function show(Anime $anime, Episode $episode)
    {
        $this->ensureEpisodeBelongsToAnime($anime, $episode);

        return redirect()->route('admin.anime.episodes.edit', [$anime, $episode]);
    }

    public function create(Anime $anime)
    {
        return view('admin.episodes.form', [
            'anime' => $anime,
            'episode' => null,
            'languages' => self::LANGUAGES,
        ]);
    }

    public function store(StoreEpisodeRequest $request, Anime $anime)
    {
        try {
            $data = $request->validated();
            $data['anime_id'] = $anime->id;
            $data['has_sub'] = $request->boolean('has_sub');
            $data['has_dub'] = $request->boolean('has_dub');
            $data['created_by'] = auth()->id();
            $data['source_type'] = $this->normalizeSourceType($data['source_type'] ?? 'upload');

            $this->handleFileUploads($request, $anime, $data);
            $this->enrichWithSourceMetadata($request, $data);

            DB::transaction(function () use (&$episode, $data) {
                $episode = Episode::create($data);
                $this->serverBuilder->createForSource($episode, $data);
            });

            return redirect()
                ->route('admin.anime.episodes.index', $anime)
                ->with('success', 'Episode created.');
        } catch (\Throwable $e) {
            Log::error('Episode store failed', [
                'anime_id' => $anime->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create episode.');
        }
    }

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

    public function update(StoreEpisodeRequest $request, Anime $anime, Episode $episode)
    {
        $this->ensureEpisodeBelongsToAnime($anime, $episode);

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

            $this->handleFileUploads($request, $anime, $data, $episode);
            $this->enrichWithSourceMetadata($request, $data);

            DB::transaction(function () use ($episode, $data) {
                $episode->update($data);
                $episode->servers()->delete();
                $this->serverBuilder->createForSource($episode, $data);
            });

            // Cleanup old local files if they were replaced
            if (
                isset($data['video_path']) &&
                $oldVideoPath &&
                $oldVideoPath !== $data['video_path'] &&
                $oldStorageDisk === 'local'
            ) {
                Storage::disk('public')->delete($oldVideoPath);
            }

            if (
                isset($data['thumbnail']) &&
                $oldThumbnail &&
                $oldThumbnail !== $data['thumbnail'] &&
                $this->isLocalStoragePath($oldThumbnail)
            ) {
                Storage::disk('public')->delete($oldThumbnail);
            }

            return redirect()
                ->route('admin.anime.episodes.index', $anime)
                ->with('success', 'Episode updated.');
        } catch (\Throwable $e) {
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

    public function destroy(Anime $anime, Episode $episode)
    {
        $this->ensureEpisodeBelongsToAnime($anime, $episode);

        try {
            DB::transaction(function () use ($episode) {
                if ($episode->video_path && $episode->storage_disk === 'local') {
                    Storage::disk('public')->delete($episode->video_path);
                }

                if ($episode->thumbnail && $this->isLocalStoragePath($episode->thumbnail)) {
                    Storage::disk('public')->delete($episode->thumbnail);
                }

                $episode->servers()->delete();
                $episode->delete();
            });

            return redirect()
                ->route('admin.anime.episodes.index', $anime)
                ->with('success', 'Episode deleted.');
        } catch (\Throwable $e) {
            Log::error('Episode delete failed', [
                'anime_id' => $anime->id,
                'episode_id' => $episode->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to delete episode.');
        }
    }

    public function deleteVideo(Anime $anime, Episode $episode)
    {
        $this->ensureEpisodeBelongsToAnime($anime, $episode);

        try {
            if ($episode->video_path && $episode->storage_disk === 'local') {
                Storage::disk('public')->delete($episode->video_path);
            }

            $episode->update([
                'video_path' => null,
                'storage_disk' => 'local',
                'source_url' => null,
                'source_id' => null,
            ]);

            $episode->servers()->delete();

            return back()->with('success', 'Video deleted.');
        } catch (\Throwable $e) {
            Log::error('Episode video delete failed', [
                'anime_id' => $anime->id,
                'episode_id' => $episode->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to delete video.');
        }
    }

    protected function handleFileUploads(Request $request, Anime $anime, array &$data, ?Episode $episode = null): void
    {
        if ($request->hasFile('thumbnail')) {
            if (
                $episode &&
                $episode->thumbnail &&
                $this->isLocalStoragePath($episode->thumbnail)
            ) {
                Storage::disk('public')->delete($episode->thumbnail);
            }

            $data['thumbnail'] = $request->file('thumbnail')
                ->store("anime/{$anime->slug}/episodes", 'public');
        }

        if ($request->hasFile('video')) {
            if (
                $episode &&
                $episode->video_path &&
                $episode->storage_disk === 'local'
            ) {
                Storage::disk('public')->delete($episode->video_path);
            }

            $data['video_path'] = $request->file('video')
                ->store("anime/{$anime->slug}/videos", 'public');
            $data['storage_disk'] = 'local';
        } elseif ($request->filled('uploaded_video_path')) {
            $data['video_path'] = $request->input('uploaded_video_path');
            $data['storage_disk'] = 'local';
        }
    }

    protected function enrichWithSourceMetadata(Request $request, array &$data): void
    {
        $sourceType = $data['source_type'] ?? 'upload';

        if ($sourceType === 'youtube' && ($request->youtube_url || ($data['source_url'] ?? null))) {
            $url = $request->youtube_url ?? ($data['source_url'] ?? null);
            $info = $this->youtube->getVideoInfo($url);

            if ($info) {
                $data['source_id'] = $info['id'] ?? null;
                $data['source_url'] = $info['watch_url'] ?? $url;
                $data['video_path'] = $info['embed_url'] ?? null;
                $data['storage_disk'] = 'streaming';
                $data['duration'] = $data['duration'] ?? (
                    isset($info['duration']) ? (int) round($info['duration'] / 60) : null
                );
                $data['thumbnail'] = $data['thumbnail'] ?? ($info['thumbnail'] ?? null);
            }
        }

        if ($sourceType === 'telegram' && $request->filled('telegram_direct_url')) {
            $data['video_path'] = $request->input('telegram_direct_url');
            $data['source_url'] = $request->input('telegram_direct_url');
            $data['source_id'] = $request->input('telegram_file_id');
            $data['storage_disk'] = 'streaming';
            $data['duration'] = $data['duration'] ?? (
                $request->filled('telegram_duration')
                ? (int) round(((int) $request->input('telegram_duration')) / 60)
                : null
            );
            $data['thumbnail'] = $data['thumbnail'] ?? $request->input('telegram_thumb');
        }

        if ($sourceType === 'external') {
            if (!empty($data['video_path']) || !empty($data['source_url'])) {
                $data['storage_disk'] = 'streaming';
            }
        }
    }

    protected function normalizeSourceType(string $sourceType): string
    {
        return $sourceType === 'direct_url' ? 'external' : $sourceType;
    }

    protected function ensureEpisodeBelongsToAnime(Anime $anime, Episode $episode): void
    {
        abort_if($episode->anime_id !== $anime->id, 404);
    }

    protected function isLocalStoragePath(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        return !str_starts_with($path, 'http://') && !str_starts_with($path, 'https://');
    }
}
