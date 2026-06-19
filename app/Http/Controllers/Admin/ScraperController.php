<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Server;
use App\Services\TelegramService;
use App\Services\YouTubeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScraperController extends Controller
{
    public function __construct(
        protected YouTubeService $youtube,
        protected TelegramService $telegram
    ) {}

    /*
    |--------------------------------------------------------------------------
    | YouTube Preview
    |--------------------------------------------------------------------------
    */
    public function youtubePreview(Request $request)
    {
        $data = $request->validate([
            'url' => 'required|url',
        ]);

        try {
            $info = $this->youtube->getVideoInfo($data['url']);

            if (!$info) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not fetch video info.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'data' => $info,
            ]);
        } catch (\Throwable $e) {
            Log::error('YouTube preview failed', [
                'url' => $data['url'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Preview failed.',
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | YouTube Import
    |--------------------------------------------------------------------------
    */
    public function youtubeImport(Request $request)
    {
        $data = $request->validate([
            'anime_id' => 'required|exists:anime,id',
            'video_id' => 'required|string',
            'episode_number' => 'required|integer|min:1',
            'title' => 'nullable|string|max:255',
            'duration' => 'nullable|integer|min:0',
            'thumbnail' => 'nullable|string',
        ]);

        if (!auth()->check()) {
            return back()->with('error', 'Authentication required.');
        }

        $anime = Anime::findOrFail($data['anime_id']);

        if ($this->episodeExists($anime->id, $data['episode_number'])) {
            return back()->with('error', 'Episode already exists.');
        }

        try {
            DB::transaction(function () use ($data, $anime, &$episode) {

                $episode = Episode::create([
                    'anime_id' => $anime->id,
                    'number' => $data['episode_number'],
                    'title' => $data['title'] ?? "Episode {$data['episode_number']}",
                    'source_type' => 'youtube',
                    'source_id' => $data['video_id'],
                    'source_url' => "https://www.youtube.com/watch?v={$data['video_id']}",
                    'video_path' => "https://www.youtube.com/embed/{$data['video_id']}",
                    'storage_disk' => 'streaming',
                    'duration' => $data['duration'],
                    'thumbnail' => $data['thumbnail'],
                    'has_sub' => true,
                    'has_dub' => false,
                    'created_by' => auth()->id(),
                ]);

                Server::create([
                    'episode_id' => $episode->id,
                    'label' => 'YouTube',
                    'url' => $episode->video_path,
                    'type' => 'youtube',
                    'language' => 'english',
                ]);
            });

            return redirect()
                ->route('admin.anime.episodes.index', $anime)
                ->with('success', 'YouTube episode imported.');
        } catch (\Throwable $e) {
            Log::error('YouTube import failed', [
                'anime_id' => $anime->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Import failed.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Telegram Import
    |--------------------------------------------------------------------------
    */
    public function telegramImport(Request $request)
    {
        $data = $request->validate([
            'anime_id' => 'required|exists:anime,id',
            'episode_number' => 'required|integer|min:1',
            'direct_url' => 'required|url',
        ]);

        if (!auth()->check()) {
            return back()->with('error', 'Authentication required.');
        }

        $anime = Anime::findOrFail($data['anime_id']);

        if ($this->episodeExists($anime->id, $data['episode_number'])) {
            return back()->with('error', 'Episode already exists.');
        }

        try {
            DB::transaction(function () use ($data, $anime, &$episode) {

                $episode = Episode::create([
                    'anime_id' => $anime->id,
                    'number' => $data['episode_number'],
                    'title' => "Episode {$data['episode_number']}",
                    'video_path' => $data['direct_url'],
                    'storage_disk' => 'streaming',
                    'source_type' => 'telegram',
                    'source_url' => $data['direct_url'],
                    'has_sub' => true,
                    'has_dub' => false,
                    'created_by' => auth()->id(),
                ]);

                Server::create([
                    'episode_id' => $episode->id,
                    'label' => 'Telegram',
                    'url' => $data['direct_url'],
                    'type' => 'stream',
                    'language' => 'english',
                ]);
            });

            return redirect()
                ->route('admin.anime.episodes.index', $anime)
                ->with('success', 'Telegram episode imported.');
        } catch (\Throwable $e) {
            Log::error('Telegram import failed', [
                'anime_id' => $anime->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Import failed.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: Check Duplicate Episode
    |--------------------------------------------------------------------------
    */
    protected function episodeExists(int $animeId, int $number): bool
    {
        return Episode::where('anime_id', $animeId)
            ->where('number', $number)
            ->exists();
    }
}
