<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEpisodeRequest;
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
        return view('admin.episodes.index', [
            'anime' => $anime,
            'episodes' => $anime->episodes()->orderBy('number')->paginate(20),
        ]);
    }

    public function show(Anime $anime, Episode $episode)
    {
        return redirect()->route('admin.anime.episodes.edit', [$anime, $episode]);
    }

    public function create(Anime $anime)
    {
        return view('admin.episodes.form', [
            'anime' => $anime,
            'episode' => null,
            'languages' => ['english', 'japanese', 'hindi'],
        ]);
    }

    public function store(StoreEpisodeRequest $request, Anime $anime)
    {
        $data = $request->validated();
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

        return view('admin.episodes.form', [
            'anime' => $anime,
            'episode' => $episode,
            'languages' => ['english', 'japanese', 'hindi'],
        ]);
    }

    public function update(StoreEpisodeRequest $request, Anime $anime, Episode $episode)
    {
        $data = $request->validated();
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
