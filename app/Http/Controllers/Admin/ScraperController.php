<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Server;
use App\Services\TelegramService;
use App\Services\YouTubeService;
use Illuminate\Http\Request;

class ScraperController extends Controller
{
    protected YouTubeService $youtube;

    protected TelegramService $telegram;

    public function __construct(YouTubeService $youtube, TelegramService $telegram)
    {
        $this->youtube = $youtube;
        $this->telegram = $telegram;
    }

    public function youtubePreview(Request $request)
    {
        $data = $request->validate([
            'url' => 'required|url',
            'anime_id' => 'required|exists:anime,id',
            'episode_number' => 'nullable|integer',
        ]);

        $info = $this->youtube->getVideoInfo($data['url']);

        if (! $info) {
            return $request->expectsJson()
                ? response()->json(['error' => 'Could not fetch YouTube video info.'], 422)
                : back()->with('error', 'Could not fetch YouTube video info.');
        }

        if ($request->expectsJson()) {
            return response()->json($info);
        }

        $anime = Anime::findOrFail($data['anime_id']);
        $episodeNumber = $data['episode_number'] ?? null;

        return view('admin.scrapers.youtube-preview', compact('info', 'anime', 'episodeNumber'));
    }

    public function youtubeImport(Request $request)
    {
        $data = $request->validate([
            'anime_id' => 'required|exists:anime,id',
            'video_id' => 'required|string',
            'title' => 'nullable|string|max:255',
            'episode_number' => 'required|integer',
            'duration' => 'nullable|integer|min:0',
            'thumbnail' => 'nullable|string',
        ]);

        $anime = Anime::findOrFail($data['anime_id']);

        $existing = Episode::where('anime_id', $anime->id)
            ->where('number', $data['episode_number'])
            ->first();

        if ($existing) {
            return back()->with('error', "Episode {$data['episode_number']} already exists for this anime.");
        }

        $episode = Episode::create([
            'anime_id' => $anime->id,
            'number' => $data['episode_number'],
            'title' => $data['title'] ?? "Episode {$data['episode_number']}",
            'source_type' => 'youtube',
            'source_id' => $data['video_id'],
            'source_url' => "https://www.youtube.com/watch?v={$data['video_id']}",
            'duration' => $data['duration'],
            'thumbnail' => $data['thumbnail'],
            'has_sub' => false,
            'has_dub' => false,
            'created_by' => auth()->id(),
        ]);

        Server::create([
            'episode_id' => $episode->id,
            'label' => 'YouTube',
            'url' => "https://www.youtube.com/embed/{$data['video_id']}",
            'type' => 'youtube',
            'language' => 'english',
        ]);

        return redirect()->route('admin.anime.episodes.index', $anime)
            ->with('success', "Episode {$data['episode_number']} imported from YouTube.");
    }

    public function telegramPreview(Request $request)
    {
        $data = $request->validate([
            'url' => 'required|string',
        ]);

        $info = null;

        if (preg_match('/^[a-zA-Z0-9_-]+$/', $data['url'])) {
            $info = $this->telegram->resolveFileId($data['url']);
        } else {
            $info = $this->telegram->resolveMessage($data['url']);
        }

        if (! $info) {
            return response()->json(['error' => 'Could not find video. Check the URL/file_id and try again.'], 422);
        }

        return response()->json($info);
    }

    public function telegramImport(Request $request)
    {
        $data = $request->validate([
            'anime_id' => 'required|exists:anime,id',
            'episode_number' => 'required|integer',
            'direct_url' => 'required|string',
            'file_id' => 'nullable|string',
            'file_size' => 'nullable|integer',
            'duration' => 'nullable|integer',
            'thumbnail' => 'nullable|string',
            'type' => 'nullable|string',
            'title' => 'nullable|string|max:255',
            'language' => 'nullable|string|in:english,japanese,hindi',
        ]);

        $anime = Anime::findOrFail($data['anime_id']);

        $existing = Episode::where('anime_id', $anime->id)
            ->where('number', $data['episode_number'])
            ->first();

        if ($existing) {
            return back()->with('error', "Episode {$data['episode_number']} already exists for this anime.");
        }

        $language = $data['language'] ?? 'english';

        $episode = Episode::create([
            'anime_id' => $anime->id,
            'number' => $data['episode_number'],
            'title' => $data['title'] ?? "Episode {$data['episode_number']}",
            'description' => null,
            'video_path' => $data['direct_url'],
            'storage_disk' => 'streaming',
            'source_type' => 'telegram',
            'source_id' => $data['file_id'],
            'source_url' => $data['direct_url'],
            'duration' => $data['duration'] ? (int) round($data['duration'] / 60) : null,
            'thumbnail' => $data['thumbnail'],
            'has_sub' => true,
            'has_dub' => false,
            'air_date' => null,
            'created_by' => auth()->id(),
        ]);

        Server::create([
            'episode_id' => $episode->id,
            'label' => 'Telegram',
            'url' => $data['direct_url'],
            'type' => $data['type'] ?? 'mp4',
            'language' => $language,
        ]);

        return redirect()->route('admin.anime.episodes.index', $anime)
            ->with('success', "Episode {$data['episode_number']} imported from Telegram.");
    }
}
