<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Server;
use App\Services\YouTubeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScraperController extends Controller
{
    protected const TYPE_YOUTUBE = 'youtube';
    protected const TYPE_STREAM = 'stream';
    protected const TYPE_TELEGRAM = 'telegram';

    protected const DISK_STREAMING = 'streaming';

    public function __construct(
        protected YouTubeService $youtube
    ) {}

    /*
    |--------------------------------------------------------------------------
    | YOUTUBE PREVIEW
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

            $this->logError('YouTube preview failed', $e, [
                'url' => $data['url'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Preview failed.',
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | YOUTUBE IMPORT
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

        $user = $request->user();

        if (!$user) {
            return back()->with('error', 'Authentication required.');
        }

        $anime = Anime::findOrFail($data['anime_id']);

        if ($this->episodeExists($anime->id, $data['episode_number'])) {
            return back()->with('error', 'Episode already exists.');
        }

        try {
            $episode = null;

            DB::transaction(function () use ($data, $anime, $user, &$episode) {

                $episode = Episode::create([
                    'anime_id'    => $anime->id,
                    'number'      => $data['episode_number'],
                    'title'       => $data['title'] ?? "Episode {$data['episode_number']}",
                    'source_type' => self::TYPE_YOUTUBE,
                    'source_id'   => $data['video_id'],
                    'source_url'  => "https://www.youtube.com/watch?v={$data['video_id']}",
                    'video_path'  => "https://www.youtube.com/embed/{$data['video_id']}",
                    'storage_disk' => self::DISK_STREAMING,
                    'duration'    => $data['duration'],
                    'thumbnail'   => $data['thumbnail'],
                    'has_sub'     => true,
                    'has_dub'     => false,
                    'created_by'  => $user->id,
                ]);

                Server::create([
                    'episode_id' => $episode->id,
                    'label'      => 'YouTube',
                    'url'        => $episode->video_path,
                    'type'       => self::TYPE_YOUTUBE,
                    'language'   => 'english',
                ]);
            });

            return redirect()
                ->route('admin.anime.episodes.index', $anime)
                ->with('success', 'YouTube episode imported.');
        } catch (\Throwable $e) {

            $this->logError('YouTube import failed', $e, [
                'anime_id' => $anime->id,
            ]);

            return back()->with('error', 'Import failed.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TELEGRAM IMPORT
    |--------------------------------------------------------------------------
    */
    public function telegramImport(Request $request)
    {
        $data = $request->validate([
            'anime_id' => 'required|exists:anime,id',
            'episode_number' => 'required|integer|min:1',
            'direct_url' => 'required|url',
        ]);

        $user = $request->user();

        if (!$user) {
            return back()->with('error', 'Authentication required.');
        }

        $anime = Anime::findOrFail($data['anime_id']);

        if ($this->episodeExists($anime->id, $data['episode_number'])) {
            return back()->with('error', 'Episode already exists.');
        }

        try {
            $episode = null;

            DB::transaction(function () use ($data, $anime, $user, &$episode) {

                $episode = Episode::create([
                    'anime_id'     => $anime->id,
                    'number'       => $data['episode_number'],
                    'title'        => "Episode {$data['episode_number']}",
                    'video_path'   => $data['direct_url'],
                    'storage_disk' => self::DISK_STREAMING,
                    'source_type'  => self::TYPE_TELEGRAM,
                    'source_url'   => $data['direct_url'],
                    'has_sub'      => true,
                    'has_dub'      => false,
                    'created_by'   => $user->id,
                ]);

                Server::create([
                    'episode_id' => $episode->id,
                    'label'      => 'Telegram',
                    'url'        => $data['direct_url'],
                    'type'       => self::TYPE_STREAM,
                    'language'   => 'english',
                ]);
            });

            return redirect()
                ->route('admin.anime.episodes.index', $anime)
                ->with('success', 'Telegram episode imported.');
        } catch (\Throwable $e) {

            $this->logError('Telegram import failed', $e, [
                'anime_id' => $anime->id,
            ]);

            return back()->with('error', 'Import failed.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER: DUPLICATE CHECK
    |--------------------------------------------------------------------------
    */
    protected function episodeExists(int $animeId, int $number): bool
    {
        return Episode::where('anime_id', $animeId)
            ->where('number', $number)
            ->exists();
    }
}
