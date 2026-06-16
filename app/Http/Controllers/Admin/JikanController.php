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

    public function searchForm()
    {
        $lastMalId = $this->getLastMalId();
        $totalImported = $this->getTotalImported();
        $importProgress = $lastMalId ? "Resume from MAL #{$lastMalId}" : null;

        return view('admin.jikan.search', compact(
            'lastMalId',
            'totalImported',
            'importProgress'
        ));
    }

    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|max:255',
            'page' => 'nullable|integer|min:1',
        ]);

        try {
            $page = $request->integer('page', 1);
            $results = $this->jikan->searchAnime($request->q, $page);
            $pagination = $this->jikan->getPagination();
        } catch (JikanApiException $e) {
            Log::error('Jikan search failed', [
                'query' => $request->q,
                'page' => $request->page,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        $existingMalIds = Anime::whereIn('mal_id', $results->pluck('mal_id')->filter())
            ->pluck('mal_id')
            ->toArray();

        $lastMalId = $this->getLastMalId();
        $totalImported = $this->getTotalImported();

        return view('admin.jikan.search', compact(
            'results',
            'pagination',
            'existingMalIds',
            'lastMalId',
            'totalImported'
        ) + [
            'query' => $request->q,
            'currentPage' => $page,
        ]);
    }

    public function preview(int $malId)
    {
        set_time_limit(0);

        try {
            $anime = $this->jikan->getAnime($malId);
            $episodes = $this->jikan->getAllEpisodes($malId);
        } catch (JikanApiException $e) {
            Log::error('Jikan preview failed', [
                'mal_id' => $malId,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.jikan.search')
                ->with('error', $e->getMessage());
        }

        $alreadyImported = Anime::where('mal_id', $malId)->exists();

        return view('admin.jikan.preview', compact(
            'anime',
            'episodes',
            'alreadyImported'
        ));
    }

    public function import(int $malId)
    {
        set_time_limit(0);

        try {
            DB::transaction(function () use ($malId, &$anime, &$episodeCount) {
                $data = $this->jikan->getAnime($malId);
                $episodeData = $this->jikan->getAllEpisodes($malId);

                $genreIds = $this->importer->syncGenres($data['genres'] ?? []);
                $anime = $this->importer->upsertAnime($data, $genreIds);
                $episodeCount = $this->importer->upsertEpisodes($anime, $episodeData->toArray());
                $anime->update([
                    'episodes_count' => $anime->episodes()->count(),
                ]);
            });
        } catch (JikanApiException $e) {
            Log::error('Jikan import failed', [
                'mal_id' => $malId,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.jikan.search')
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Anime import transaction failed', [
                'mal_id' => $malId,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.jikan.search')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }

        $action = $anime->wasRecentlyCreated ? 'Imported' : 'Updated';

        return redirect()
            ->route('admin.anime.index')
            ->with('success', "{$action} \"{$anime->title}\" with {$episodeCount} episodes from MAL.");
    }

    public function refreshEpisodes(int $malId)
    {
        set_time_limit(0);

        $anime = Anime::where('mal_id', $malId)->first();

        if (!$anime) {
            return redirect()
                ->route('admin.jikan.search')
                ->with('error', 'Anime not found. Import it first from MAL Import.');
        }

        try {
            DB::transaction(function () use ($malId, $anime, &$oldCount, &$processed, &$newCount) {
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
                    'episodes_count' => $newCount,
                ]);
            });
        } catch (JikanApiException $e) {
            Log::error('Refresh episodes failed', [
                'mal_id' => $malId,
                'anime_id' => $anime->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Refresh episodes transaction failed', [
                'mal_id' => $malId,
                'anime_id' => $anime->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to save episodes: ' . $e->getMessage());
        }

        $added = max($newCount - $oldCount, 0);
        $updated = max($processed - $added, 0);

        return redirect()
            ->route('admin.anime.episodes.index', $anime)
            ->with('success', "Episodes refreshed for \"{$anime->title}\". {$added} new, {$updated} updated. Total: {$newCount} episodes.");
    }

    public function batchImport(Request $request)
    {
        $request->validate([
            'batch_size' => 'nullable|integer|min:1|max:50',
            'with_episodes' => 'nullable|boolean',
        ]);

        $batchSize = $request->integer('batch_size', 10);
        $fetchEpisodes = $request->boolean('with_episodes', false);
        $resumeMalId = $this->getLastMalId();

        $page = 1;
        $dispatched = 0;
        $skipped = 0;

        $existingMalIds = Anime::whereNotNull('mal_id')
            ->pluck('mal_id')
            ->toArray();

        while ($dispatched < $batchSize) {
            try {
                $results = $this->jikan->browseAnime($page);
            } catch (JikanApiException $e) {
                Log::error('Batch import browse failed', [
                    'page' => $page,
                    'error' => $e->getMessage(),
                ]);

                return redirect()
                    ->route('admin.jikan.search')
                    ->with('error', $e->getMessage());
            }

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

                if (in_array($malId, $existingMalIds, true)) {
                    $skipped++;
                    continue;
                }

                ImportAnimeJob::dispatch($data, $fetchEpisodes);

                $dispatched++;
                $existingMalIds[] = $malId;
            }

            $pagination = $this->jikan->getPagination();

            if (!($pagination['has_next_page'] ?? false)) {
                break;
            }

            $page++;
        }

        return redirect()
            ->route('admin.jikan.search')
            ->with('success', "Dispatched {$dispatched} anime for import. {$skipped} skipped (already imported). Run `php artisan queue:work` to process the queue.");
    }

    public function refreshAnime(int $malId)
    {
        set_time_limit(0);

        $anime = Anime::where('mal_id', $malId)->first();

        if (!$anime) {
            return redirect()
                ->route('admin.jikan.search')
                ->with('error', 'Anime not found. Import it first from MAL Import.');
        }

        try {
            DB::transaction(function () use ($malId, &$anime, &$oldEpCount, &$processed, &$newEpCount) {
                $data = $this->jikan->getAnime($malId);
                $episodeData = $this->jikan->getAllEpisodes($malId);

                $oldEpCount = $anime->episodes()->count();

                $genreIds = $this->importer->syncGenres($data['genres'] ?? []);
                $anime = $this->importer->upsertAnime($data, $genreIds);

                $processed = $this->importer->upsertEpisodes(
                    $anime,
                    $episodeData->toArray(),
                    false,
                    true
                );

                $newEpCount = $anime->episodes()->count();

                $anime->update([
                    'episodes_count' => $newEpCount,
                ]);
            });
        } catch (JikanApiException $e) {
            Log::error('Refresh anime failed', [
                'mal_id' => $malId,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Refresh anime transaction failed', [
                'mal_id' => $malId,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Refresh failed: ' . $e->getMessage());
        }

        $added = max($newEpCount - $oldEpCount, 0);
        $updated = max($processed - $added, 0);

        return redirect()
            ->route('admin.anime.index')
            ->with('success', "Refreshed \"{$anime->title}\" from MAL. {$added} new episodes, {$updated} updated. Total: {$newEpCount} episodes.");
    }

    public function resetProgress()
    {
        Setting::where('key', 'jikan_last_mal_id')->delete();

        return redirect()
            ->route('admin.jikan.search')
            ->with('success', 'Import progress has been reset.');
    }

    protected function getLastMalId(): ?string
    {
        return Setting::where('key', 'jikan_last_mal_id')->value('value');
    }

    protected function getTotalImported(): int
    {
        return Anime::whereNotNull('mal_id')->count();
    }
}
