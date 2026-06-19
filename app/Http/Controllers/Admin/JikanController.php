<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\JikanApiException;
use App\Http\Controllers\Controller;
use App\Jobs\ImportAnimeJob;
use App\Models\Anime;
use App\Models\Setting;
use App\Services\JikanImporter;
use App\Services\JikanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JikanController extends Controller
{
    public function __construct(
        protected JikanService $jikan,
        protected JikanImporter $importer,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | SEARCH UI
    |--------------------------------------------------------------------------
    */
    public function searchForm()
    {
        return view('admin.jikan.search', [
            'lastMalId' => $this->getLastMalId(),
            'totalImported' => $this->getTotalImported(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|max:255',
        ]);

        try {
            $results = $this->jikan->searchAnime($request->input('q'));
            $pagination = $this->jikan->getPagination();

        } catch (JikanApiException $e) {
            $this->logError('Jikan search failed', $e);

            return back()->with('error', $e->getMessage());
        }

        $existingMalIds = Anime::whereIn(
            'mal_id',
            collect($results)->pluck('mal_id')->filter()
        )->pluck('mal_id')->all();

        return view('admin.jikan.search', compact(
            'results',
            'pagination',
            'existingMalIds'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | PREVIEW
    |--------------------------------------------------------------------------
    */
    public function preview(int $malId)
    {
        try {
            $anime = $this->jikan->getAnime($malId);
            $episodes = $this->jikan->getAllEpisodes($malId);

        } catch (JikanApiException $e) {
            $this->logError('Jikan preview failed', $e);

            return back()->with('error', $e->getMessage());
        }

        $alreadyImported = Anime::where('mal_id', $malId)->exists();

        return view('admin.jikan.preview', compact(
            'anime',
            'episodes',
            'alreadyImported'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT SINGLE ANIME
    |--------------------------------------------------------------------------
    */
    public function import(int $malId)
    {
        try {
            $anime = null;
            $episodeCount = 0;

            DB::transaction(function () use ($malId, &$anime, &$episodeCount) {

                $data = $this->jikan->getAnime($malId);
                $episodeData = $this->jikan->getAllEpisodes($malId);

                $genreIds = $this->importer->syncGenres($data['genres'] ?? []);

                $anime = $this->importer->upsertAnime($data, $genreIds);

                $episodeCount = $this->importer->upsertEpisodes(
                    $anime,
                    $episodeData->toArray()
                );

                $anime->update([
                    'episodes_count' => $anime->episodes()->count(),
                ]);
            });

        } catch (\Throwable $e) {

            $this->logError('Anime import failed', $e, [
                'mal_id' => $malId,
            ]);

            return back()->with('error', 'Import failed.');
        }

        return redirect()
            ->route('admin.anime.index')
            ->with('success', "{$anime->title} imported with {$episodeCount} episodes.");
    }

    /*
    |--------------------------------------------------------------------------
    | REFRESH EPISODES ONLY
    |--------------------------------------------------------------------------
    */
    public function refreshEpisodes(int $malId)
    {
        $anime = Anime::where('mal_id', $malId)->first();

        if (!$anime) {
            return back()->with('error', 'Anime not found.');
        }

        try {
            $oldCount = $anime->episodes()->count();

            $episodeData = $this->jikan->getAllEpisodes($malId);

            $this->importer->upsertEpisodes(
                $anime,
                $episodeData->toArray(),
                false,
                true
            );

            $newCount = $anime->episodes()->count();

            $anime->update([
                'episodes_count' => $newCount,
            ]);

        } catch (\Throwable $e) {

            $this->logError('Episode refresh failed', $e, [
                'mal_id' => $malId,
            ]);

            return back()->with('error', 'Refresh failed.');
        }

        return back()->with(
            'success',
            'New episodes: ' . max($newCount - $oldCount, 0)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BATCH IMPORT (QUEUE)
    |--------------------------------------------------------------------------
    */
    public function batchImport(Request $request)
    {
        $batch = max(1, (int) $request->input('batch_size', 10));

        try {
            $results = $this->jikan->browseAnime(1);
        } catch (\Throwable $e) {
            $this->logError('Batch fetch failed', $e);

            return back()->with('error', 'Failed to fetch anime.');
        }

        $existing = Anime::whereIn(
            'mal_id',
            collect($results)->pluck('mal_id')
        )->pluck('mal_id')->flip();

        $dispatched = 0;

        foreach ($results as $data) {

            if ($dispatched >= $batch) break;

            $malId = $data['mal_id'] ?? null;

            if (!$malId || isset($existing[$malId])) {
                continue;
            }

            ImportAnimeJob::dispatch($data);
            $dispatched++;
        }

        return back()->with(
            'success',
            "{$dispatched} anime queued successfully."
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RESET IMPORT PROGRESS
    |--------------------------------------------------------------------------
    */
    public function resetProgress()
    {
        Setting::where('key', 'jikan_last_mal_id')->delete();

        return back()->with('success', 'Import progress reset.');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    protected function getLastMalId()
    {
        return Setting::where('key', 'jikan_last_mal_id')->value('value');
    }

    protected function getTotalImported(): int
    {
        return Anime::whereNotNull('mal_id')->count();
    }
}