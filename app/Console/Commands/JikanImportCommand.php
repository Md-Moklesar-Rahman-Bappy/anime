<?php

namespace App\Console\Commands;

use App\Models\Anime;
use App\Models\Setting;
use App\Services\JikanImporter;
use App\Services\JikanService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class JikanImportCommand extends Command
{
    protected $signature = 'jikan:import
        {query?}
        {--mal-id=}
        {--top}
        {--filter=all}
        {--limit=25}
        {--seasonal}
        {--year=}
        {--season=}
        {--all}
        {--episodes}
        {--batch=100}
        {--force}
        {--dry-run}';

    protected $description = 'Import anime from Jikan API';

    public function __construct(
        protected JikanService $jikan,
        protected JikanImporter $importer
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            if ($this->option('all')) {
                return $this->importAll();
            }

            if ($this->option('top')) {
                return $this->importTop();
            }

            if ($this->option('seasonal')) {
                return $this->importSeasonal();
            }

            if ($this->option('mal-id')) {
                return $this->importById((int) $this->option('mal-id'));
            }

            if ($query = $this->argument('query')) {
                return $this->searchAndImport($query);
            }

            $this->error('Provide query or option.');
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            logger()->error('Jikan import failed', [
                'error' => $e->getMessage(),
            ]);

            return Command::FAILURE;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT ALL (RESUMABLE + SAFE)
    |--------------------------------------------------------------------------
    */
    protected function importAll(): int
    {
        $batchSize = max(1, (int) $this->option('batch'));
        $fetchEpisodes = (bool) $this->option('episodes');
        $dryRun = (bool) $this->option('dry-run');

        $page = max(1, (int) (Setting::where('key', 'jikan_import_page')->value('value') ?: 1));

        $imported = 0;
        $skipped = 0;

        while (true) {

            $this->info("Processing page: {$page}");

            $results = collect($this->jikan->browseAnime($page));

            if ($results->isEmpty()) {
                break;
            }

            foreach ($results as $data) {

                $malId = (int) ($data['mal_id'] ?? 0);

                if (!$malId) {
                    $skipped++;
                    continue;
                }

                // ✅ optimized existence check
                if (Anime::where('mal_id', $malId)->exists()) {
                    $skipped++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("[DRY-RUN] {$data['title']} ({$malId})");
                    $imported++;
                    continue;
                }

                $episodes = $fetchEpisodes
                    ? $this->safeFetchEpisodes($malId)
                    : collect();

                $this->importAnime($data, $episodes);

                $imported++;

                // ✅ batch break
                if ($imported >= $batchSize) {
                    Setting::updateOrCreate(
                        ['key' => 'jikan_import_page'],
                        ['value' => $page]
                    );

                    $this->showImportSummary($imported, $skipped);
                    return Command::SUCCESS;
                }
            }

            // ✅ move to next page safely
            $nextPage = $page + 1;

            Setting::updateOrCreate(
                ['key' => 'jikan_import_page'],
                ['value' => $nextPage]
            );

            $pagination = $this->safeBrowsePagination($page);

            if (!($pagination['has_next_page'] ?? false)) {
                Setting::where('key', 'jikan_import_page')->delete();
                break;
            }

            $page = $nextPage;

            usleep(300000); // ✅ API safe delay
        }

        $this->showImportSummary($imported, $skipped);

        return Command::SUCCESS;
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT SINGLE
    |--------------------------------------------------------------------------
    */
    protected function importById(int $malId): int
    {
        if ($malId <= 0) {
            return Command::FAILURE;
        }

        if (Anime::where('mal_id', $malId)->exists()) {
            $this->warn("Already exists.");
            return Command::SUCCESS;
        }

        $data = $this->jikan->getAnime($malId);

        if (!$data) {
            return Command::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->line("[DRY-RUN] {$data['title']}");
            return Command::SUCCESS;
        }

        $episodes = $this->safeFetchEpisodes($malId);

        $this->importAnime($data, $episodes);

        return Command::SUCCESS;
    }

    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */
    protected function searchAndImport(string $query): int
    {
        $results = collect($this->jikan->searchAnime($query));

        if ($results->isEmpty()) {
            return Command::FAILURE;
        }

        $first = $results->first();
        $malId = (int) ($first['mal_id'] ?? 0);

        if (!$malId) {
            return Command::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->line("[DRY-RUN] {$first['title']}");
            return Command::SUCCESS;
        }

        $this->importAnime(
            $first,
            $this->safeFetchEpisodes($malId)
        );

        return Command::SUCCESS;
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT TOP
    |--------------------------------------------------------------------------
    */
    protected function importTop(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $filter = (string) $this->option('filter');

        $results = collect($this->jikan->getTopAnime($filter, 1, $limit));

        foreach ($results as $data) {

            $malId = (int) ($data['mal_id'] ?? 0);

            if (!$malId || Anime::where('mal_id', $malId)->exists()) {
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("[DRY-RUN] {$data['title']}");
                continue;
            }

            $this->importAnime(
                $data,
                $this->safeFetchEpisodes($malId)
            );
        }

        return Command::SUCCESS;
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT SEASONAL
    |--------------------------------------------------------------------------
    */
    protected function importSeasonal(): int
    {
        $year = (int) ($this->option('year') ?: date('Y'));
        $season = strtolower($this->option('season') ?: $this->getCurrentSeason());

        $results = collect($this->jikan->getSeasonalAnime($year, $season));

        foreach ($results as $data) {

            $malId = (int) ($data['mal_id'] ?? 0);

            if (!$malId || Anime::where('mal_id', $malId)->exists()) {
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("[DRY-RUN] {$data['title']}");
                continue;
            }

            $this->importAnime(
                $data,
                $this->safeFetchEpisodes($malId)
            );
        }

        return Command::SUCCESS;
    }

    /*
    |--------------------------------------------------------------------------
    | CORE IMPORT
    |--------------------------------------------------------------------------
    */
    protected function importAnime(array $data, mixed $episodes): void
    {
        DB::transaction(function () use ($data, $episodes) {

            $genreIds = $this->importer->syncGenres($data['genres'] ?? []);

            $anime = $this->importer->upsertAnime($data, $genreIds);

            $episodeArray = $episodes instanceof Collection
                ? $episodes->toArray()
                : (is_array($episodes) ? $episodes : []);

            if (!empty($episodeArray)) {
                $this->importer->upsertEpisodes($anime, $episodeArray);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | SAFE HELPERS
    |--------------------------------------------------------------------------
    */
    protected function safeFetchEpisodes(int $malId): Collection
    {
        try {
            return collect($this->jikan->getAllEpisodes($malId));
        } catch (\Throwable) {
            return collect();
        }
    }

    protected function safeBrowsePagination(int $page): array
    {
        try {
            return $this->jikan->browsePagination($page) ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    protected function showImportSummary(int $imported, int $skipped): void
    {
        $this->info("Imported: {$imported} | Skipped: {$skipped}");
    }

    protected function getCurrentSeason(): string
    {
        return match (true) {
            date('n') <= 3 => 'winter',
            date('n') <= 6 => 'spring',
            date('n') <= 9 => 'summer',
            default => 'fall',
        };
    }
}
