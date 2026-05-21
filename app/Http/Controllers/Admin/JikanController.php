<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportAnimeJob;
use App\Models\Anime;
use App\Models\Setting;
use App\Services\JikanImporter;
use App\Services\JikanService;
use Illuminate\Http\Request;

class JikanController extends Controller
{
    protected JikanService $jikan;
    protected JikanImporter $importer;

    public function __construct(JikanService $jikan, JikanImporter $importer)
    {
        $this->jikan = $jikan;
        $this->importer = $importer;
    }

    public function searchForm()
    {
        $lastMalId = Setting::where('key', 'jikan_last_mal_id')->value('value');
        $totalImported = Anime::whereNotNull('mal_id')->count();
        $importProgress = $lastMalId ? "Resume from MAL #{$lastMalId}" : null;

        return view('admin.jikan.search', compact('lastMalId', 'totalImported', 'importProgress'));
    }

    public function search(Request $request)
    {
        $request->validate(['q' => 'required|string|max:255']);

        $page = $request->integer('page', 1);
        $results = $this->jikan->searchAnime($request->q, $page);
        $pagination = $this->jikan->lastPagination;

        if ($this->jikan->lastError) {
            return back()->withInput()->with('error', 'Jikan API error: '.$this->jikan->lastError);
        }

        $existingMalIds = Anime::whereIn('mal_id', $results->pluck('mal_id')->filter())
            ->pluck('mal_id')
            ->toArray();

        $lastMalId = Setting::where('key', 'jikan_last_mal_id')->value('value');
        $totalImported = Anime::whereNotNull('mal_id')->count();

        return view('admin.jikan.search', compact(
            'results', 'pagination', 'existingMalIds', 'lastMalId', 'totalImported'
        ) + [
            'query' => $request->q,
            'currentPage' => $page,
        ]);
    }

    public function preview(int $malId)
    {
        $anime = $this->jikan->getAnime($malId);

        if ($this->jikan->lastError) {
            return redirect()->route('admin.jikan.search')
                ->with('error', 'Jikan API error: '.$this->jikan->lastError);
        }

        if (! $anime) {
            return redirect()->route('admin.jikan.search')
                ->with('error', 'Anime not found on MyAnimeList.');
        }

        $episodes = $this->jikan->getAnimeEpisodes($malId);
        $alreadyImported = Anime::where('mal_id', $malId)->exists();

        return view('admin.jikan.preview', compact('anime', 'episodes', 'alreadyImported'));
    }

    public function import(int $malId)
    {
        if (Anime::where('mal_id', $malId)->exists()) {
            return redirect()->route('admin.jikan.search')
                ->with('error', 'This anime has already been imported.');
        }

        $data = $this->jikan->getAnime($malId);

        if ($this->jikan->lastError) {
            return redirect()->route('admin.jikan.search')
                ->with('error', 'Jikan API error: '.$this->jikan->lastError);
        }

        if (! $data) {
            return redirect()->route('admin.jikan.search')
                ->with('error', 'Failed to fetch anime data from MyAnimeList.');
        }

        $episodeData = $this->jikan->getAllEpisodes($malId);

        $genreIds = $this->importer->syncGenres($data['genres']);
        $anime = $this->importer->upsertAnime($data, $genreIds);
        $episodeCount = $this->importer->upsertEpisodes($anime, $episodeData->toArray());
        $anime->update(['episodes_count' => $anime->episodes()->count()]);

        $action = $anime->wasRecentlyCreated ? 'Imported' : 'Updated';

        return redirect()->route('admin.anime.index')
            ->with('success', "{$action} \"{$anime->title}\" with {$episodeCount} episodes from MAL.");
    }

    public function batchImport(Request $request)
    {
        $request->validate([
            'batch_size' => 'integer|min:1|max:50',
            'with_episodes' => 'boolean',
        ]);

        $batchSize = $request->integer('batch_size', 10);
        $fetchEpisodes = $request->boolean('with_episodes', false);
        $resumeMalId = Setting::where('key', 'jikan_last_mal_id')->value('value');
        $page = 1;
        $dispatched = 0;
        $skipped = 0;

        $existingMalIds = Anime::whereNotNull('mal_id')->pluck('mal_id')->toArray();

        while ($dispatched < $batchSize) {
            $results = $this->jikan->browseAnime($page);

            if ($results->isEmpty()) {
                break;
            }

            foreach ($results as $data) {
                if ($dispatched >= $batchSize) {
                    break 2;
                }

                $malId = $data['mal_id'];

                if ($resumeMalId && $malId <= (int) $resumeMalId) {
                    $skipped++;
                    continue;
                }

                if (in_array($malId, $existingMalIds)) {
                    $skipped++;
                    continue;
                }

                ImportAnimeJob::dispatch($data, $fetchEpisodes);
                $dispatched++;
                $existingMalIds[] = $malId;
            }

            $pagination = $this->jikan->lastPagination;
            if (! ($pagination['has_next_page'] ?? false)) {
                break;
            }
            $page++;
        }

        return redirect()->route('admin.jikan.search')
            ->with('success', "Dispatched {$dispatched} anime for import. {$skipped} skipped (already imported). Run `php artisan queue:work` to process the queue.");
    }

    public function resetProgress()
    {
        Setting::where('key', 'jikan_last_mal_id')->delete();

        return redirect()->route('admin.jikan.search')
            ->with('success', 'Import progress has been reset.');
    }
}
