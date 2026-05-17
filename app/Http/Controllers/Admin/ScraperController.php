<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Scrapers\ScraperManager;
use App\Services\YouTubeService;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Server;
use Illuminate\Http\Request;

class ScraperController extends Controller
{
    protected ScraperManager $scraperManager;
    protected YouTubeService $youtube;

    public function __construct(ScraperManager $scraperManager, YouTubeService $youtube)
    {
        $this->scraperManager = $scraperManager;
        $this->youtube = $youtube;
    }

    public function searchForm(Request $request)
    {
        $scrapers = $this->scraperManager->names();
        $animeId = $request->query('anime_id');
        $anime = $animeId ? Anime::find($animeId) : null;
        return view('admin.scrapers.search', compact('scrapers', 'anime'));
    }

    public function search(Request $request)
    {
        $data = $request->validate([
            'scraper' => 'required|string',
            'query' => 'required|string|max:255',
        ]);

        $scraper = $this->scraperManager->get($data['scraper']);
        if (!$scraper) {
            return back()->with('error', 'Invalid scraper.');
        }

        $results = $scraper->search($data['query']);
        $scrapers = $this->scraperManager->names();

        return view('admin.scrapers.search', compact('scrapers', 'results', 'scraper'));
    }

    public function previewEpisodes(Request $request)
    {
        $data = $request->validate([
            'scraper' => 'required|string',
            'anime_id' => 'required|string',
            'anime_title' => 'nullable|string',
            'anime_image' => 'nullable|string',
            'local_anime_id' => 'nullable|exists:anime,id',
        ]);

        $scraper = $this->scraperManager->get($data['scraper']);
        if (!$scraper) {
            return back()->with('error', 'Invalid scraper.');
        }

        $episodes = $scraper->getEpisodes($data['anime_id']);

        return view('admin.scrapers.episodes', compact('scraper', 'episodes', 'data'));
    }

    public function importEpisodes(Request $request)
    {
        $data = $request->validate([
            'scraper' => 'required|string',
            'anime_id' => 'required|string',
            'anime_title' => 'nullable|string',
            'local_anime_id' => 'nullable|exists:anime,id',
            'episodes' => 'required|array',
            'episodes.*.id' => 'required|string',
            'episodes.*.number' => 'required|integer',
        ]);

        $scraper = $this->scraperManager->get($data['scraper']);
        if (!$scraper) {
            return back()->with('error', 'Invalid scraper.');
        }

        if (!empty($data['local_anime_id'])) {
            $anime = Anime::findOrFail($data['local_anime_id']);
        } else {
            $anime = Anime::where('title', $data['anime_title'])
                ->orWhere('title', 'like', '%' . $data['anime_title'] . '%')
                ->first();

            if (!$anime) {
                return back()->with('error', 'Anime not found. Please create it first.');
            }
        }

        $imported = 0;
        foreach ($data['episodes'] as $ep) {
            $existing = Episode::where('anime_id', $anime->id)
                ->where('number', $ep['number'])
                ->first();

            if ($existing) continue;

            $sourceUrl = $scraper->getVideoUrl($ep['id']);
            if (!$sourceUrl) continue;

            $episode = Episode::create([
                'anime_id' => $anime->id,
                'number' => $ep['number'],
                'title' => "Episode {$ep['number']}",
                'source_type' => 'scraper',
                'source_id' => $ep['id'],
                'source_url' => $sourceUrl,
                'has_sub' => true,
                'has_dub' => false,
                'created_by' => auth()->id(),
            ]);

            Server::create([
                'episode_id' => $episode->id,
                'label' => $scraper->name(),
                'url' => $sourceUrl,
                'type' => 'embed',
            ]);

            $imported++;
        }

        return redirect()->route('admin.anime.episodes.index', $anime)
            ->with('success', "Imported {$imported} episodes from {$scraper->name()}.");
    }

    public function youtubePreview(Request $request)
    {
        $data = $request->validate([
            'url' => 'required|url',
            'anime_id' => 'required|exists:anime,id',
            'episode_number' => 'nullable|integer',
        ]);

        $info = $this->youtube->getVideoInfo($data['url']);

        if (!$info) {
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
            'duration' => 'nullable|integer',
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
        ]);

        return redirect()->route('admin.anime.episodes.index', $anime)
            ->with('success', "Episode {$data['episode_number']} imported from YouTube.");
    }
}
