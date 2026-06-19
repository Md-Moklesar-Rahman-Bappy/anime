<?php

namespace App\Console\Commands;

use App\Models\Anime;
use App\Models\Setting;
use App\Services\JikanImporter;
use App\Services\JikanService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class JikanImportCommand extends Command
{
    protected $signature = 'jikan:import
        {query? : Search query for finding anime}
        {--mal-id= : Import by specific MAL ID}
        {--top : Import top anime}
        {--filter=all : Filter for top anime: airing, upcoming, bypopularity, favorite, all}
        {--limit=25 : Number of anime to import}
        {--seasonal : Import seasonal anime}
        {--year= : Year for seasonal import}
        {--season= : Season for seasonal import: winter, spring, summer, fall}
        {--all : Import all anime from Jikan, paginated and resumable}
        {--episodes : Also fetch episodes when using --all}
        {--batch=100 : Number of anime to import per run when using --all}
        {--force : Skip confirmation prompts}
        {--dry-run : Preview what would be imported without saving}';

    protected $description = 'Import anime from MyAnimeList via the Jikan API';

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
                return $this->searchAndImport((string) $query);
            }

            $this->error('Please provide one of: query, --mal-id, --top, --seasonal, or --all.');

            return Command::FAILURE;
        } catch (Throwable $e) {
            $this->error('Import failed: ' . $e->getMessage());

            Log::error('Jikan import command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            report($e);

            return Command::FAILURE;
        }
    }

    protected function importAll(): int
    {
        $batchSize = max(1, (int) $this->option('batch'));
        $fetchEpisodes = (bool) $this->option('episodes');
        $dryRun = (bool) $this->option('dry-run');

        $startPage = (int) (Setting::where('key', 'jikan_import_page')->value('value') ?: 1);
        $page = max(1, $startPage);

        $imported = 0;
        $skipped = 0;

        $this->info("Starting full Jikan import from page {$page}.");
        $this->line("Batch size: {$batchSize}");
        $this->line('Fetch episodes: ' . ($fetchEpisodes ? 'yes' : 'no'));
        $this->line('Dry run: ' . ($dryRun ? 'yes' : 'no'));

        while (true) {
            $this->newLine();
            $this->info("Fetching page {$page}...");

            $results = $this->jikan->browseAnime($page);

            if (!$results instanceof Collection) {
                $results = collect($results);
            }

            if ($results->isEmpty()) {
                $this->info('No more results found.');
                break;
            }

            foreach ($results as $data) {
                $malId = (int) ($data['mal_id'] ?? 0);
                $title = $data['title'] ?? 'Unknown title';

                if (!$malId) {
                    $this->warn("Skipping item without MAL ID: {$title}");
                    $skipped++;
                    continue;
                }

                if (Anime::where('mal_id', $malId)->exists()) {
                    $this->line("Skipping existing: {$title} MAL #{$malId}");
                    $skipped++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("[DRY-RUN] Would import: {$title} MAL #{$malId}");
                    $imported++;

                    if ($imported >= $batchSize) {
                        $this->showImportSummary($imported, $skipped);
                        return Command::SUCCESS;
                    }

                    continue;
                }

                $this->line("Importing: {$title} MAL #{$malId}");

                $episodes = $fetchEpisodes
                    ? $this->safeFetchEpisodes($malId)
                    : collect();

                $this->importAnime($data, $episodes);

                $imported++;

                if ($imported >= $batchSize) {
                    Setting::updateOrCreate(
                        ['key' => 'jikan_import_page'],
                        ['value' => $page]
                    );

                    $this->info("Reached batch limit of {$batchSize}. Run the command again to continue.");
                    $this->showImportSummary($imported, $skipped);

                    return Command::SUCCESS;
                }

                $this->sleepBetweenRequests();
            }

            Setting::updateOrCreate(
                ['key' => 'jikan_import_page'],
                ['value' => $page + 1]
            );

            $pagination = $this->safeBrowsePagination($page);

            if (!($pagination['has_next_page'] ?? false)) {
                $this->info('All available pages processed.');

                if (!$dryRun) {
                    Setting::where('key', 'jikan_import_page')->delete();
                }

                break;
            }

            $page++;
            $this->sleepBetweenRequests();
        }

        $this->showImportSummary($imported, $skipped);

        return Command::SUCCESS;
    }

    protected function importById(int $malId): int
    {
        if ($malId <= 0) {
            $this->error('Invalid MAL ID.');
            return Command::FAILURE;
        }

        if (Anime::where('mal_id', $malId)->exists()) {
            $this->warn("MAL ID {$malId} is already imported.");
            return Command::SUCCESS;
        }

        $this->info("Fetching anime MAL #{$malId}...");

        $data = $this->jikan->getAnime($malId);

        if (!$data) {
            $this->error("Anime with MAL ID {$malId} was not found.");
            return Command::FAILURE;
        }

        $title = $data['title'] ?? 'Unknown title';

        $this->line("Found: {$title}");

        if ($this->option('dry-run')) {
            $this->line("[DRY-RUN] Would import: {$title}");
            return Command::SUCCESS;
        }

        if (!$this->option('force') && $this->input->isInteractive()) {
            if (!$this->confirm("Import \"{$title}\"?", true)) {
                $this->warn('Import cancelled.');
                return Command::SUCCESS;
            }
        }

        $episodes = $this->safeFetchEpisodes($malId);

        $this->importAnime($data, $episodes);

        $this->info("Import complete: {$title}");

        return Command::SUCCESS;
    }

    protected function searchAndImport(string $query): int
    {
        $query = trim($query);

        if ($query === '') {
            $this->error('Search query cannot be empty.');
            return Command::FAILURE;
        }

        $this->info("Searching Jikan for: {$query}");

        $results = $this->jikan->searchAnime($query);

        if (!$results instanceof Collection) {
            $results = collect($results);
        }

        if ($results->isEmpty()) {
            $this->error("No anime found for query: {$query}");
            return Command::FAILURE;
        }

        $first = $results->first();

        $malId = (int) ($first['mal_id'] ?? 0);
        $title = $first['title'] ?? 'Unknown title';

        if (!$malId) {
            $this->error('The selected result does not have a valid MAL ID.');
            return Command::FAILURE;
        }

        if (Anime::where('mal_id', $malId)->exists()) {
            $this->warn("\"{$title}\" is already imported.");
            return Command::SUCCESS;
        }

        $this->line("First result: {$title} MAL #{$malId}");

        if ($this->option('dry-run')) {
            $this->line("[DRY-RUN] Would import: {$title}");
            return Command::SUCCESS;
        }

        if (!$this->option('force') && $this->input->isInteractive()) {
            if (!$this->confirm("Import \"{$title}\"?", true)) {
                $this->warn('Import cancelled.');
                return Command::SUCCESS;
            }
        }

        $episodes = $this->safeFetchEpisodes($malId);

        $this->importAnime($first, $episodes);

        $this->info("Import complete: {$title}");

        return Command::SUCCESS;
    }

    protected function importTop(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $filter = (string) $this->option('filter');
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Fetching top anime. Filter: {$filter}, Limit: {$limit}");

        $results = $this->jikan->getTopAnime($filter, 1, $limit);

        if (!$results instanceof Collection) {
            $results = collect($results);
        }

        if ($results->isEmpty()) {
            $this->error('No top anime results found.');
            return Command::FAILURE;
        }

        $imported = 0;
        $skipped = 0;

        foreach ($results as $data) {
            $malId = (int) ($data['mal_id'] ?? 0);
            $title = $data['title'] ?? 'Unknown title';

            if (!$malId) {
                $skipped++;
                continue;
            }

            if (Anime::where('mal_id', $malId)->exists()) {
                $this->line("Skipping existing: {$title}");
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line("[DRY-RUN] Would import: {$title}");
                $imported++;
                continue;
            }

            $this->line("Importing: {$title}");

            $episodes = $this->safeFetchEpisodes($malId);

            $this->importAnime($data, $episodes);

            $imported++;
            $this->sleepBetweenRequests();
        }

        $this->showImportSummary($imported, $skipped);

        return Command::SUCCESS;
    }

    protected function importSeasonal(): int
    {
        $year = (int) ($this->option('year') ?: date('Y'));
        $season = strtolower((string) ($this->option('season') ?: $this->getCurrentSeason()));
        $dryRun = (bool) $this->option('dry-run');

        $allowedSeasons = ['winter', 'spring', 'summer', 'fall'];

        if (!in_array($season, $allowedSeasons, true)) {
            $this->error('Invalid season. Allowed values: winter, spring, summer, fall.');
            return Command::FAILURE;
        }

        $this->info("Fetching seasonal anime: {$season} {$year}");

        $results = $this->jikan->getSeasonalAnime($year, $season);

        if (!$results instanceof Collection) {
            $results = collect($results);
        }

        if ($results->isEmpty()) {
            $this->error("No seasonal anime found for {$season} {$year}.");
            return Command::FAILURE;
        }

        $imported = 0;
        $skipped = 0;

        foreach ($results as $data) {
            $malId = (int) ($data['mal_id'] ?? 0);
            $title = $data['title'] ?? 'Unknown title';

            if (!$malId) {
                $skipped++;
                continue;
            }

            if (Anime::where('mal_id', $malId)->exists()) {
                $this->line("Skipping existing: {$title}");
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line("[DRY-RUN] Would import: {$title}");
                $imported++;
                continue;
            }

            $this->line("Importing: {$title}");

            $episodes = $this->safeFetchEpisodes($malId);

            $this->importAnime($data, $episodes);

            $imported++;
            $this->sleepBetweenRequests();
        }

        $this->showImportSummary($imported, $skipped);

        return Command::SUCCESS;
    }

    protected function importAnime(array $data, mixed $episodes): void
    {
        DB::transaction(function () use ($data, $episodes) {
            $genreIds = $this->importer->syncGenres($data['genres'] ?? []);

            $anime = $this->importer->upsertAnime($data, $genreIds);

            if ($episodes instanceof Collection) {
                $episodeArray = $episodes->toArray();
            } elseif (is_array($episodes)) {
                $episodeArray = $episodes;
            } else {
                $episodeArray = [];
            }

            $this->importer->upsertEpisodes($anime, $episodeArray);

            $episodeCount = method_exists($anime, 'episodes')
                ? $anime->episodes()->count()
                : count($episodeArray);

            $this->info("Imported: {$anime->title} ({$episodeCount} episodes)");
        });
    }

    protected function safeFetchEpisodes(int $malId): Collection
    {
        try {
            $episodes = $this->jikan->getAllEpisodes($malId);

            return $episodes instanceof Collection
                ? $episodes
                : collect($episodes);
        } catch (Throwable $e) {
            Log::warning('Failed to fetch Jikan episodes', [
                'mal_id' => $malId,
                'error' => $e->getMessage(),
            ]);

            $this->warn("Could not fetch episodes for MAL #{$malId}. Continuing without episodes.");

            return collect();
        }
    }

    protected function safeBrowsePagination(int $page): array
    {
        try {
            $pagination = $this->jikan->browsePagination($page);

            return is_array($pagination) ? $pagination : [];
        } catch (Throwable $e) {
            Log::warning('Failed to fetch Jikan pagination', [
                'page' => $page,
                'error' => $e->getMessage(),
            ]);

            return [
                'has_next_page' => false,
            ];
        }
    }

    protected function sleepBetweenRequests(): void
    {
        usleep(350000);
    }

    protected function showImportSummary(int $imported, int $skipped): void
    {
        $this->newLine();
        $this->info("Done. Imported: {$imported}, Skipped: {$skipped}");
    }

    protected function getCurrentSeason(): string
    {
        $month = (int) date('n');

        return match (true) {
            $month <= 3 => 'winter',
            $month <= 6 => 'spring',
            $month <= 9 => 'summer',
            default => 'fall',
        };
    }
}
