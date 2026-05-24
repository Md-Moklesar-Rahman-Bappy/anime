<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Server;
use App\Services\YouTubeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EpisodeController extends Controller
{
    protected YouTubeService $youtube;

    public function __construct(YouTubeService $youtube)
    {
        $this->youtube = $youtube;
    }

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
            'source_type' => 'nullable|string|in:upload,youtube,telegram,external,scraper',
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

        $data['anime_id'] = $anime->id;
        $data['has_sub'] = $request->has('has_sub');
        $data['has_dub'] = $request->has('has_dub');
        $data['created_by'] = auth()->id();
        $data['source_type'] = $data['source_type'] ?? 'upload';

        // remap legacy source types
        if ($data['source_type'] === 'direct_url') {
            $data['source_type'] = 'external';
        }

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

        if ($data['source_type'] === 'youtube' && ($request->youtube_url || $data['source_url'])) {
            $url = $request->youtube_url ?? $data['source_url'];
            $info = $this->youtube->getVideoInfo($url);
            if ($info) {
                $data['source_id'] = $info['id'];
                $data['source_url'] = $info['watch_url'];
                $data['video_path'] = $info['embed_url'];
                $data['storage_disk'] = 'streaming';
                $data['duration'] = $data['duration'] ?? (int) round($info['duration'] / 60);
                $data['thumbnail'] = $data['thumbnail'] ?? $info['thumbnail'];
            } else {
                return back()->withInput()->with('error', 'Could not fetch YouTube video info. Check the URL and try again.');
            }
        }

        if ($data['source_type'] === 'telegram' && $request->telegram_direct_url) {
            $data['video_path'] = $request->telegram_direct_url;
            $data['source_url'] = $request->telegram_direct_url;
            $data['source_id'] = $request->telegram_file_id;
            $data['storage_disk'] = 'streaming';
            $data['duration'] = $data['duration'] ?? ($request->telegram_duration ? (int) round($request->telegram_duration / 60) : null);
            $data['thumbnail'] = $data['thumbnail'] ?? $request->telegram_thumb;
        }

        if ($data['source_type'] === 'external' && $request->video_path) {
            $data['storage_disk'] = 'streaming';
        }

        $episode = Episode::create($data);

        $this->createServerForSource($episode, $data);

        return redirect()->route('admin.anime.episodes.index', $anime)
            ->with('success', 'Episode created.');
    }

    protected function createServerForSource(Episode $episode, array $data): void
    {
        if (empty($data['video_path']) && empty($data['source_url'])) {
            return;
        }

        $sourceType = $data['source_type'] ?? 'upload';
        $url = $data['video_path'] ?? $data['source_url'];
        $language = $data['language'] ?? 'english';

        switch ($sourceType) {
            case 'youtube':
                Server::create([
                    'episode_id' => $episode->id,
                    'label' => 'YouTube',
                    'url' => $url,
                    'type' => 'youtube',
                    'language' => $language,
                ]);
                break;

            case 'upload':
                $ext = strtolower(pathinfo($data['video_path'], PATHINFO_EXTENSION));
                $type = in_array($ext, ['mp4', 'webm', 'mkv']) ? $ext : 'mp4';
                Server::create([
                    'episode_id' => $episode->id,
                    'label' => 'Upload',
                    'url' => url('storage/'.$data['video_path']),
                    'type' => $type,
                    'language' => $language,
                ]);
                break;

            case 'external':
                $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
                $type = match ($ext) {
                    'm3u8' => 'm3u8',
                    'mp4', 'webm', 'mkv' => $ext,
                    default => 'embed',
                };
                $label = $data['source_label'] ?? 'Server';
                Server::create([
                    'episode_id' => $episode->id,
                    'label' => $label,
                    'url' => $url,
                    'type' => $type,
                    'language' => $language,
                ]);
                break;

            case 'telegram':
                Server::create([
                    'episode_id' => $episode->id,
                    'label' => 'Telegram',
                    'url' => $url,
                    'type' => 'mp4',
                    'language' => $language,
                ]);
                break;
        }
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
            'source_type' => 'nullable|string|in:upload,youtube,telegram,external,scraper',
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

        // remap legacy source types
        if ($data['source_type'] === 'direct_url') {
            $data['source_type'] = 'external';
        }

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

        if ($data['source_type'] === 'youtube' && ($request->youtube_url || $data['source_url'])) {
            $url = $request->youtube_url ?? $data['source_url'];
            $info = $this->youtube->getVideoInfo($url);
            if ($info) {
                $data['source_id'] = $info['id'];
                $data['source_url'] = $info['watch_url'];
                $data['video_path'] = $info['embed_url'];
                $data['storage_disk'] = 'streaming';
                $data['duration'] = $data['duration'] ?? (int) round($info['duration'] / 60);
                $data['thumbnail'] = $data['thumbnail'] ?? $info['thumbnail'];
            } else {
                return back()->withInput()->with('error', 'Could not fetch YouTube video info. Check the URL and try again.');
            }
        }

        if ($data['source_type'] === 'telegram' && $request->telegram_direct_url) {
            $data['video_path'] = $request->telegram_direct_url;
            $data['source_url'] = $request->telegram_direct_url;
            $data['source_id'] = $request->telegram_file_id;
            $data['storage_disk'] = 'streaming';
            $data['duration'] = $data['duration'] ?? ($request->telegram_duration ? (int) round($request->telegram_duration / 60) : null);
            $data['thumbnail'] = $data['thumbnail'] ?? $request->telegram_thumb;
        }

        if ($data['source_type'] === 'external' && $request->video_path) {
            $data['storage_disk'] = 'streaming';
        }

        $episode->update($data);

        $episode->servers()->delete();

        $this->createServerForSource($episode, $data);

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
}
