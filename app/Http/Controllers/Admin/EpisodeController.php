<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Episode;
use App\Services\ServerBuilderService;
use App\Services\YouTubeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EpisodeController extends Controller
{
    public function __construct(
        protected YouTubeService $youtube,
        protected ServerBuilderService $serverBuilder,
    ) {}

    public function index(Anime $anime)
    {
        $episodes = $anime->episodes()->orderBy('number')->paginate(20);

        return view('admin.episodes.index', compact('anime', 'episodes'));
    }

    public function show(Anime $anime, Episode $episode)
    {
        return redirect()->route('admin.anime.episodes.edit', [$anime, $episode]);
    }

    public function create(Anime $anime)
    {
        $languages = ['english', 'japanese', 'hindi'];
        $episode = null;

        return view('admin.episodes.form', compact('anime', 'episode', 'languages'));
    }

    public function store(Request $request, Anime $anime)
    {
        $data = $request->validate([
            'number' => 'required|integer',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'video_path' => 'nullable|string',
            'storage_disk' => 'nullable|string|in:local,s3,streaming',
            'source_type' => 'nullable|string|in:upload,youtube,telegram,external',
            'source_id' => 'nullable|string',
            'source_url' => 'nullable|string',
            'duration' => 'nullable|integer',
            'thumbnail' => 'nullable|image|max:2048',
            'has_sub' => 'nullable|boolean',
            'has_dub' => 'nullable|boolean',
            'air_date' => 'nullable|date',
            'language' => 'nullable|string',
            'youtube_url' => 'nullable|url',
            'uploaded_video_path' => 'nullable|string',
            'telegram_direct_url' => 'nullable|string',
            'telegram_file_id' => 'nullable|string',
            'telegram_duration' => 'nullable|integer',
            'telegram_thumb' => 'nullable|string',
            'source_label' => 'nullable|string|max:255',
        ]);

        $data['anime_id'] = $anime->id;
        $data['has_sub'] = $request->has('has_sub');
        $data['has_dub'] = $request->has('has_dub');
        $data['created_by'] = auth()->id();
        $data['source_type'] = $data['source_type'] ?? 'upload';

        if ($data['source_type'] === 'direct_url') {
            $data['source_type'] = 'external';
        }

        $this->handleFileUploads($request, $anime, $data);

        $this->enrichWithSourceMetadata($request, $data);

        $episode = Episode::create($data);

        $this->serverBuilder->createForSource($episode, $data);

        return redirect()->route('admin.anime.episodes.index', $anime)
            ->with('success', 'Episode created.');
    }

    public function edit(Anime $anime, Episode $episode)
    {
        $episode->load('servers');
        $languages = ['english', 'japanese', 'hindi'];

        return view('admin.episodes.form', compact('anime', 'episode', 'languages'));
    }

    public function update(Request $request, Anime $anime, Episode $episode)
    {
        $data = $request->validate([
            'number' => 'required|integer',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'video_path' => 'nullable|string',
            'storage_disk' => 'nullable|string|in:local,s3,streaming',
            'source_type' => 'nullable|string|in:upload,youtube,telegram,external',
            'source_id' => 'nullable|string',
            'source_url' => 'nullable|string',
            'duration' => 'nullable|integer',
            'thumbnail' => 'nullable|image|max:2048',
            'has_sub' => 'nullable|boolean',
            'has_dub' => 'nullable|boolean',
            'air_date' => 'nullable|date',
            'youtube_url' => 'nullable|url',
            'uploaded_video_path' => 'nullable|string',
            'language' => 'nullable|string|in:english,japanese,hindi',
            'source_label' => 'nullable|string|max:255',
            'telegram_direct_url' => 'nullable|string',
            'telegram_file_id' => 'nullable|string',
            'telegram_file_size' => 'nullable|integer',
            'telegram_duration' => 'nullable|integer',
            'telegram_thumb' => 'nullable|string',
            'telegram_type' => 'nullable|string',
        ]);

        $data['has_sub'] = $request->has('has_sub');
        $data['has_dub'] = $request->has('has_dub');
        $data['source_type'] = $data['source_type'] ?? $episode->source_type ?? 'upload';

        if ($data['source_type'] === 'direct_url') {
            $data['source_type'] = 'external';
        }

        $this->handleFileUploads($request, $anime, $data);

        $this->enrichWithSourceMetadata($request, $data);

        $episode->update($data);

        $episode->servers()->delete();

        $this->serverBuilder->createForSource($episode, $data);

        return redirect()->route('admin.anime.episodes.index', $anime)
            ->with('success', 'Episode updated.');
    }

    public function destroy(Anime $anime, Episode $episode)
    {
        if ($episode->video_path && $episode->storage_disk === 'local') {
            Storage::disk('public')->delete($episode->video_path);
        }
        $episode->delete();

        return redirect()->route('admin.anime.episodes.index', $anime)
            ->with('success', 'Episode deleted.');
    }

    public function deleteVideo(Anime $anime, Episode $episode)
    {
        if ($episode->video_path && $episode->storage_disk === 'local') {
            Storage::disk('public')->delete($episode->video_path);
        }
        $episode->update(['video_path' => null, 'storage_disk' => 'local']);
        $episode->servers()->delete();

        return back()->with('success', 'Video deleted.');
    }

    protected function handleFileUploads(Request $request, Anime $anime, array &$data): void
    {
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')
                ->store("anime/{$anime->slug}/episodes", 'public');
        }

        if ($request->hasFile('video')) {
            $data['video_path'] = $request->file('video')
                ->store("anime/{$anime->slug}/videos", 'public');
            $data['storage_disk'] = 'local';
        } elseif ($request->uploaded_video_path) {
            $data['video_path'] = $request->uploaded_video_path;
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
                $data['source_id'] = $info['id'];
                $data['source_url'] = $info['watch_url'];
                $data['video_path'] = $info['embed_url'];
                $data['storage_disk'] = 'streaming';
                $data['duration'] = $data['duration'] ?? (int) round($info['duration'] / 60);
                $data['thumbnail'] = $data['thumbnail'] ?? $info['thumbnail'];
            }
        }

        if ($sourceType === 'telegram' && $request->telegram_direct_url) {
            $data['video_path'] = $request->telegram_direct_url;
            $data['source_url'] = $request->telegram_direct_url;
            $data['source_id'] = $request->telegram_file_id;
            $data['storage_disk'] = 'streaming';
            $data['duration'] = $data['duration'] ?? ($request->telegram_duration ? (int) round($request->telegram_duration / 60) : null);
            $data['thumbnail'] = $data['thumbnail'] ?? $request->telegram_thumb;
        }

        if ($sourceType === 'external' && isset($data['video_path'])) {
            $data['storage_disk'] = 'streaming';
        }
    }
}
