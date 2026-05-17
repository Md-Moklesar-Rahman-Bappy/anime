<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Server;
use App\Services\YouTubeService;
use Illuminate\Http\Request;

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

    public function create(Anime $anime)
    {
        return view('admin.episodes.form', compact('anime'));
    }

    public function store(Request $request, Anime $anime)
    {
        $data = $request->validate([
            'number' => 'required|integer',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'video_path' => 'nullable|string',
            'storage_disk' => 'nullable|string|in:local,s3,streaming',
            'source_type' => 'nullable|string|in:upload,direct_url,youtube,servers,scraper',
            'source_id' => 'nullable|string',
            'source_url' => 'nullable|string',
            'duration' => 'nullable|integer',
            'thumbnail' => 'nullable|image|max:2048',
            'has_sub' => 'nullable|boolean',
            'has_dub' => 'nullable|boolean',
            'air_date' => 'nullable|date',
            'youtube_url' => 'nullable|url',
        ]);

        $data['anime_id'] = $anime->id;
        $data['has_sub'] = $request->has('has_sub');
        $data['has_dub'] = $request->has('has_dub');
        $data['created_by'] = auth()->id();
        $data['source_type'] = $data['source_type'] ?? 'upload';

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')
                ->store("anime/{$anime->slug}/episodes", 'public');
        }

        if ($request->hasFile('video')) {
            $data['video_path'] = $request->file('video')
                ->store("anime/{$anime->slug}/videos", 'public');
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
                $data['duration'] = $data['duration'] ?? $info['duration'];
                $data['thumbnail'] = $data['thumbnail'] ?? $info['thumbnail'];
            }
        }

        $episode = Episode::create($data);

        if ($data['source_type'] === 'youtube' && !empty($episode->video_path)) {
            Server::create([
                'episode_id' => $episode->id,
                'label' => 'YouTube',
                'url' => $episode->video_path,
                'type' => 'youtube',
            ]);
        }

        if ($request->server_label) {
            foreach ($request->server_label as $i => $label) {
                if (!empty($request->server_url[$i])) {
                    Server::create([
                        'episode_id' => $episode->id,
                        'label' => $label,
                        'url' => $request->server_url[$i],
                        'type' => $request->server_type[$i] ?? 'mp4',
                    ]);
                }
            }
        }

        return redirect()->route('admin.anime.episodes.index', $anime)
            ->with('success', 'Episode created.');
    }

    public function edit(Anime $anime, Episode $episode)
    {
        $episode->load('servers');
        return view('admin.episodes.form', compact('anime', 'episode'));
    }

    public function update(Request $request, Anime $anime, Episode $episode)
    {
        $data = $request->validate([
            'number' => 'required|integer',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'video_path' => 'nullable|string',
            'storage_disk' => 'nullable|string|in:local,s3,streaming',
            'source_type' => 'nullable|string|in:upload,direct_url,youtube,servers,scraper',
            'source_id' => 'nullable|string',
            'source_url' => 'nullable|string',
            'duration' => 'nullable|integer',
            'thumbnail' => 'nullable|image|max:2048',
            'has_sub' => 'nullable|boolean',
            'has_dub' => 'nullable|boolean',
            'air_date' => 'nullable|date',
            'youtube_url' => 'nullable|url',
        ]);

        $data['has_sub'] = $request->has('has_sub');
        $data['has_dub'] = $request->has('has_dub');
        $data['source_type'] = $data['source_type'] ?? $episode->source_type ?? 'upload';

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')
                ->store("anime/{$anime->slug}/episodes", 'public');
        }

        if ($request->hasFile('video')) {
            $data['video_path'] = $request->file('video')
                ->store("anime/{$anime->slug}/videos", 'public');
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
                $data['duration'] = $data['duration'] ?? $info['duration'];
                $data['thumbnail'] = $data['thumbnail'] ?? $info['thumbnail'];
            }
        }

        $episode->update($data);

        $episode->servers()->delete();

        if ($data['source_type'] === 'youtube' && !empty($episode->video_path)) {
            Server::create([
                'episode_id' => $episode->id,
                'label' => 'YouTube',
                'url' => $episode->video_path,
                'type' => 'youtube',
            ]);
        }

        if ($request->server_label) {
            foreach ($request->server_label as $i => $label) {
                if (!empty($request->server_url[$i])) {
                    Server::create([
                        'episode_id' => $episode->id,
                        'label' => $label,
                        'url' => $request->server_url[$i],
                        'type' => $request->server_type[$i] ?? 'mp4',
                    ]);
                }
            }
        }

        return redirect()->route('admin.anime.episodes.index', $anime)
            ->with('success', 'Episode updated.');
    }

    public function destroy(Anime $anime, Episode $episode)
    {
        $episode->delete();
        return redirect()->route('admin.anime.episodes.index', $anime)
            ->with('success', 'Episode deleted.');
    }
}
