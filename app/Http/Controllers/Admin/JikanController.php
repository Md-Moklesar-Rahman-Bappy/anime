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
use Illuminate\Support\Facades\Log;

class JikanController extends Controller
{
    public function __construct(
        protected JikanService $jikan,
        protected JikanImporter $importer,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Search UI
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
    | Search Anime
    |--------------------------------------------------------------------------
    */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|max:255',
        ]);

        try {
            $results = $this->jikan->searchAnime($request->q);
            $pagination = $this->jikan->getPagination();
        } catch (JikanApiException $e) {
            return back()->with('error', $e->getMessage());
        }

        $existingMalIds = Anime::whereIn('mal_id', $results->pluck('mal_id')->filter())
            ->pluck('mal_id')
            ->toArray();

        return view('admin.jikan.search', compact(
            'results',
            'pagination',
            'existingMalIds'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Preview Anime
    |--------------------------------------------------------------------------
    */
    public function preview(int $malId)
    {
        try {
            $anime = $this->jikan->getAnime($malId);
            $episodes = $this->jikan->getAllEpisodes($malId);
        } catch (JikanApiException $e) {
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
    | Import Single Anime
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
                    'episodes_count' => $anime->episodes()->count()
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Import failed', [
                'mal_id' => $malId,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Import failed.');
        }

        return redirect()
            ->route('admin.anime.index')
            ->with('success', "Imported {$anime->title} ({$episodeCount} episodes)");
    }

    /*
    |--------------------------------------------------------------------------
    | Refresh Episodes Only
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

            $processed = $this->importer->upsertEpisodes(
                $anime,
                $episodeData->toArray(),
                false,
                true
            );

            $newCount = $anime->episodes()->count();

            $anime->update([
                'episodes_count' => $newCount
            ]);
        } catch (\Throwable $e) {
            Log::error('Refresh episodes failed', [
                'mal_id' => $malId,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Refresh failed.');
        }

        return back()->with(
            'success',
            "Episodes updated. New: " . max($newCount - $oldCount, 0)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Batch Import (Queue)
    |--------------------------------------------------------------------------
    */
    public function batchImport(Request $request)
    {
        $batch = $request->integer('batch_size', 10);

        $results = $this->jikan->browseAnime(1);

        $dispatched = 0;

        foreach ($results as $data) {

            if ($dispatched >= $batch) break;

            if (Anime::where('mal_id', $data['mal_id'])->exists()) {
                continue;
            }

            ImportAnimeJob::dispatch($data);

            $dispatched++;
        }

        return back()->with(
            'success',
            "{$dispatched} anime queued. Run queue worker."
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Reset Import Progress
    |--------------------------------------------------------------------------
    */
    public function resetProgress()
    {
        Setting::where('key', 'jikan_last_mal_id')->delete();

        return back()->with('success', 'Progress reset.');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
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
