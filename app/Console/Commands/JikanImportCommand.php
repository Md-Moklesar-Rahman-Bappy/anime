<?php

namespace App\Console\Commands;

use App\Models\Anime;
use App\Models\Setting;
use App\Services\JikanImporter;
use App\Services\JikanService;
use Illuminate\Console\Command;

class JikanImportCommand extends Command
{
    protected $signature = 'jikan:import
        {query? : Search query for finding anime}
        {--mal-id= : Import by specific MAL ID}
        {--top : Import top anime}
        {--filter=all : Filter for top anime (airing, upcoming, bypopularity, favorite)}
        {--limit=25 : Number of anime to import}
        {--seasonal : Import current seasonal anime}
        {--year= : Year for seasonal import}
        {--season= : Season for seasonal import (winter, spring, summer, fall)}
        {--all : Import all anime from Jikan (paginated, resumable)}
        {--episodes : Also fetch episodes when using --all}
        {--batch=100 : Number of anime per batch when using --all}
        {--dry-run : Preview what would be imported without saving}';

    protected $description = 'Import anime from MyAnimeList via the Jikan API';

    protected JikanService $jikan;
    protected JikanImporter $importer;

    public function __construct(JikanService $jikan, JikanImporter $importer)
    {
        parent::__construct();
        $this->jikan = $jikan;
        $this->importer = $importer;
    }

    public function handle(): int
    {
        if ($this->option('all')) {
            return $this->importAll();
        }

        if ($this->option('top')) {
            return $this->importTop();
        }

        if ($this->option('seasonal')) {
            return $this->importSeasonal();
        }

        if ($malId = $this->option('mal-id')) {
            return $this->importById((int) $malId);
        }

        if ($query = $this->argument('query')) {
            return $this->searchAndImport($query);
        }

        $this->error('Provide a query, --mal-id, --top, --seasonal, or --all.');

        return Command::FAILURE;
    }

    protected function importAll(): int
    {
        $batchSize = (int) $this->option('batch');
        $fetchEpisodes = (bool) $this->option('episodes');

        $resumeMalId = Setting::where('key', 'jikan_last_mal_id')->value('value');
        $startPage = 1;

        if ($resumeMalId) {
            $this->info("Resuming from MAL ID {$resumeMalId}...");
        }

        $this->line("Fetching all anime from Jikan (batch: {$batchSize})...");

        $imported = 0;
        $skipped = 0;
        $page = $startPage;
        $pastResumePoint = ! $resumeMalId;

        while (true) {
            $results = $this->jikan->browseAnime($page);

            if ($results->isEmpty()) {
                $this->info('No more results. Complete!');
                break;
            }

            foreach ($results as $data) {
                $malId = $data['mal_id'];

                if ($resumeMalId && $malId <= (int) $resumeMalId) {
                    $skipped++;

                    continue;
                }

                $pastResumePoint = true;

                if (Anime::where('mal_id', $malId)->exists()) {
                    $skipped++;

                    continue;
                }

                if ($this->option('dry-run')) {
                    $this->line("[DRY-RUN] Would import: {$data['title']} (MAL #{$malId})");
                    $imported++;
                    Setting::updateOrCreate(['key' => 'jikan_last_mal_id'], ['value' => $malId]);

                    continue;
                }

                $this->line("Importing: {$data['title']} (MAL #{$malId})...");

                $episodes = $fetchEpisodes ? $this->jikan->getAllEpisodes($malId) : collect();
                $this->importAnime($data, $episodes);

                Setting::updateOrCreate(['key' => 'jikan_last_mal_id'], ['value' => $malId]);
                $imported++;

                if ($imported >= $batchSize) {
                    $this->info("Reached batch limit of {$batchSize}. Run again to continue.");
                    $this->info("Imported: {$imported}, Skipped: {$skipped}");

                    return Command::SUCCESS;
                }
            }

            $pagination = $this->jikan->browsePagination($page + 1);
            if (! ($pagination['has_next_page'] ?? false)) {
                $this->info('All pages imported!');
                break;
            }

            $page++;
        }

        Setting::where('key', 'jikan_last_mal_id')->delete();
        $this->info("Done! Imported: {$imported}, Skipped (already exist): {$skipped}");

        return Command::SUCCESS;
    }

    protected function importById(int $malId): int
    {
        if (Anime::where('mal_id', $malId)->exists()) {
            $this->warn("MAL ID {$malId} is already imported.");

            return Command::FAILURE;
        }

        $this->info("Fetching MAL ID {$malId}...");
        $data = $this->jikan->getAnime($malId);

        if (! $data) {
            $this->error("Anime with MAL ID {$malId} not found.");

            return Command::FAILURE;
        }

        $episodes = $this->jikan->getAllEpisodes($malId);
        $this->line("Found: {$data['title']} ({$data['episodes_count']} eps)");

        if ($this->option('dry-run')) {
            $this->line('Dry-run: would import this anime.');

            return Command::SUCCESS;
        }

        $this->importAnime($data, $episodes);

        return Command::SUCCESS;
    }

    protected function searchAndImport(string $query): int
    {
        $this->info("Searching for \"{$query}\"...");
        $results = $this->jikan->searchAnime($query);

        if ($results->isEmpty()) {
            $this->error("No results for \"{$query}\".");

            return Command::FAILURE;
        }

        $first = $results->first();

        if (Anime::where('mal_id', $first['mal_id'])->exists()) {
            $this->warn("\"{$first['title']}\" is already imported.");

            return Command::FAILURE;
        }

        $this->line("Found: {$first['title']} (MAL ID: {$first['mal_id']}, {$first['episodes_count']} eps)");

        if ($this->option('dry-run')) {
            $this->line('Dry-run: would import this anime.');

            return Command::SUCCESS;
        }

        if (! $this->confirm("Import \"{$first['title']}\"?", true)) {
            return Command::FAILURE;
        }

        $episodes = $this->jikan->getAllEpisodes($first['mal_id']);
        $this->importAnime($first, $episodes);

        return Command::SUCCESS;
    }

    protected function importTop(): int
    {
        $limit = (int) $this->option('limit');
        $filter = $this->option('filter');

        $this->info("Fetching top {$limit} anime (filter: {$filter})...");
        $results = $this->jikan->getTopAnime($filter, 1, $limit);

        if ($results->isEmpty()) {
            $this->error('No results.');

            return Command::FAILURE;
        }

        $imported = 0;
        foreach ($results as $data) {
            if (Anime::where('mal_id', $data['mal_id'])->exists()) {
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("[DRY-RUN] Would import: {$data['title']}");
                $imported++;

                continue;
            }

            $this->line("Importing: {$data['title']}...");
            $episodes = $this->jikan->getAllEpisodes($data['mal_id']);
            $this->importAnime($data, $episodes);
            $imported++;
        }

        $this->info("Imported {$imported} anime.");

        return Command::SUCCESS;
    }

    protected function importSeasonal(): int
    {
        $year = $this->option('year') ?: (int) date('Y');
        $season = $this->option('season') ?: $this->getCurrentSeason();

        $this->info("Fetching {$season} {$year} seasonal anime...");
        $results = $this->jikan->getSeasonalAnime($year, $season);

        if ($results->isEmpty()) {
            $this->error("No results for {$season} {$year}.");

            return Command::FAILURE;
        }

        $imported = 0;
        foreach ($results as $data) {
            if (Anime::where('mal_id', $data['mal_id'])->exists()) {
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("[DRY-RUN] Would import: {$data['title']}");
                $imported++;

                continue;
            }

            $this->line("Importing: {$data['title']}...");
            $episodes = $this->jikan->getAllEpisodes($data['mal_id']);
            $this->importAnime($data, $episodes);
            $imported++;
        }

        $this->info("Imported {$imported} anime.");

        return Command::SUCCESS;
    }

    protected function importAnime(array $data, $episodes): void
    {
        $genreIds = $this->importer->syncGenres($data['genres']);
        $anime = $this->importer->upsertAnime($data, $genreIds);
        $this->importer->upsertEpisodes($anime, $episodes);

        $this->info("Imported: {$anime->title} ({$anime->episodes()->count()} episodes)");
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
