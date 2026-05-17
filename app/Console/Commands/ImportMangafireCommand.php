<?php

namespace App\Console\Commands;

use App\Models\Chapter;
use App\Models\Manga;
use App\Models\MangaGenre;
use App\Models\Setting;
use App\Services\Scrapers\MangafireScraper;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportMangafireCommand extends Command
{
    protected $signature = 'mangafire:import
        {--catalog : Only sync the catalog from filter pages (fast)}
        {--details : Process manga that have no details yet}
        {--chapters : Import chapters for manga that have no chapters yet}
        {--all : Run full import (catalog + details + chapters)}
        {--page= : Start from a specific filter page}
        {--limit= : Max number of manga to process in this run}
        {--resume : Auto-resume from last position}
        {--delay=1.5 : Seconds between requests}
        {--dry-run : Preview without saving}';

    protected $description = 'Import manga and chapters from Mangafire.to';

    protected MangafireScraper $scraper;
    protected int $imported = 0;
    protected int $skipped = 0;
    protected int $totalPages = 0;

    public function __construct(MangafireScraper $scraper)
    {
        parent::__construct();
        $this->scraper = $scraper;
    }

    public function handle(): int
    {
        $delay = (float) $this->option('delay');
        $this->scraper->setRequestDelay((int) ($delay * 1000000));

        $mode = 'all';
        if ($this->option('catalog')) {
            $mode = 'catalog';
        } elseif ($this->option('details')) {
            $mode = 'details';
        } elseif ($this->option('chapters')) {
            $mode = 'chapters';
        } elseif ($this->option('all')) {
            $mode = 'all';
        }

        if ($mode === 'all' || $mode === 'catalog') {
            $this->importCatalog();
        }

        if ($mode === 'all' || $mode === 'details') {
            $this->importDetails();
        }

        if ($mode === 'all' || $mode === 'chapters') {
            $this->importChapters();
        }

        $this->info("Done! Imported: {$this->imported}, Skipped: {$this->skipped}");

        return Command::SUCCESS;
    }

    protected function importCatalog(): void
    {
        $this->info('Phase 1: Importing catalog from filter pages...');
        $this->totalPages = $this->scraper->getTotalPages();
        $this->info("Found {$this->totalPages} pages of manga on Mangafire.");

        $startPage = 1;
        if ($this->option('resume')) {
            $lastPage = (int) Setting::where('key', 'mf_last_catalog_page')->value('value');
            if ($lastPage) {
                $startPage = $lastPage + 1;
                $this->info("Resuming from page {$startPage}...");
            }
        }

        if ($this->option('page')) {
            $startPage = (int) $this->option('page');
        }

        $limit = $this->option('limit') ? (int) $this->option('limit') : PHP_INT_MAX;
        $page = $startPage;

        while ($page <= $this->totalPages && $this->imported < $limit) {
            $this->line("  Page {$page}/{$this->totalPages}...");

            if ($this->option('dry-run')) {
                $mangaList = $this->scraper->scrapePage($page);
                $this->line("    [DRY-RUN] Would import {$this->imported}+ manga from this page");
                $this->imported += count($mangaList);
                Setting::updateOrCreate(['key' => 'mf_last_catalog_page'], ['value' => $page]);
                $page++;
                continue;
            }

            $mangaList = $this->scraper->scrapePage($page);

            foreach ($mangaList as $data) {
                if ($this->imported >= $limit) {
                    break 2;
                }

                if (Manga::where('source_id', $data['slug'])->exists()) {
                    $this->skipped++;
                    continue;
                }

                $slug = Str::slug($data['title']);
                $originalSlug = $slug;
                $counter = 1;
                while (Manga::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $counter++;
                }

                Manga::create([
                    'title' => $data['title'],
                    'slug' => $slug,
                    'type' => $data['type'] ? ucfirst(strtolower($data['type'])) : null,
                    'thumbnail' => $data['thumbnail'],
                    'source' => 'mangafire',
                    'source_id' => $data['slug'],
                ]);

                $this->imported++;
            }

            Setting::updateOrCreate(['key' => 'mf_last_catalog_page'], ['value' => $page]);
            $page++;
        }

        if ($page > $this->totalPages) {
            Setting::where('key', 'mf_last_catalog_page')->delete();
        }

        $this->info("  Catalog phase done. Imported: {$this->imported}, Skipped: {$this->skipped}");
    }

    protected function importDetails(): void
    {
        $this->info('Phase 2: Importing manga details...');

        $mangaQuery = Manga::where('source', 'mangafire')
            ->whereNull('description');

        if ($this->option('resume')) {
            $lastSlug = Setting::where('key', 'mf_last_detail_slug')->value('value');
            if ($lastSlug) {
                $mangaQuery->where('source_id', '>', $lastSlug);
            }
        }

        $limit = $this->option('limit') ? (int) $this->option('limit') : PHP_INT_MAX;
        $mangaList = $mangaQuery->orderBy('source_id')->limit($limit)->get();
        $total = $mangaList->count();
        $this->info("  Processing {$total} manga details...");

        $done = 0;
        foreach ($mangaList as $manga) {
            $done++;
            $this->line("  [{$done}/{$total}] Fetching details: {$manga->title}...");

            if ($this->option('dry-run')) {
                $this->line("    [DRY-RUN] Would fetch details for {$manga->title}");
                Setting::updateOrCreate(['key' => 'mf_last_detail_slug'], ['value' => $manga->source_id]);
                continue;
            }

            $detail = $this->scraper->scrapeMangaDetail($manga->source_id);
            if (! $detail) {
                $this->warn("    Failed to fetch details for {$manga->title}");
                continue;
            }

            $genreIds = [];
            foreach ($detail['genres'] as $genreName) {
                $genreSlug = Str::slug($genreName);
                $genre = MangaGenre::firstOrCreate(
                    ['slug' => $genreSlug],
                    ['name' => $genreName]
                );
                $genreIds[] = $genre->id;
            }

            $manga->update([
                'description' => $detail['description'],
                'alternative_titles' => $detail['alternative_titles'],
                'type' => $detail['type'] ?: $manga->type,
                'status' => $detail['status'],
                'year' => $detail['year'],
                'rating' => $detail['rating'],
                'score' => $detail['score'],
                'chapters_count' => $detail['chapters_count'],
                'author' => $detail['author'],
                'artist' => $detail['artist'],
                'thumbnail' => $detail['thumbnail'] ?: $manga->thumbnail,
                'banner' => $detail['banner'],
            ]);

            if (! empty($genreIds)) {
                $manga->genres()->syncWithoutDetaching($genreIds);
            }

            $this->imported++;
            Setting::updateOrCreate(['key' => 'mf_last_detail_slug'], ['value' => $manga->source_id]);
        }

        if ($done >= $total) {
            Setting::where('key', 'mf_last_detail_slug')->delete();
        }

        $this->info("  Details phase done. Processed: {$done}");
    }

    protected function importChapters(): void
    {
        $this->info('Phase 3: Importing chapters...');

        $mangaQuery = Manga::where('source', 'mangafire')
            ->whereNotNull('description');

        if ($this->option('resume')) {
            $lastSlug = Setting::where('key', 'mf_last_chapter_slug')->value('value');
            if ($lastSlug) {
                $mangaQuery->where('source_id', '>', $lastSlug);
            }
        }

        $limit = $this->option('limit') ? (int) $this->option('limit') : PHP_INT_MAX;
        $mangaList = $mangaQuery->orderBy('source_id')->limit($limit)->get();
        $total = $mangaList->count();
        $this->info("  Processing chapters for {$total} manga...");

        $done = 0;
        foreach ($mangaList as $manga) {
            $done++;

            if ($manga->chapters()->exists()) {
                $this->line("  [{$done}/{$total}] Skipping (already has chapters): {$manga->title}");
                $this->skipped++;
                Setting::updateOrCreate(['key' => 'mf_last_chapter_slug'], ['value' => $manga->source_id]);
                continue;
            }

            $this->line("  [{$done}/{$total}] Fetching chapters: {$manga->title}...");

            if ($this->option('dry-run')) {
                $this->line("    [DRY-RUN] Would fetch chapters for {$manga->title}");
                Setting::updateOrCreate(['key' => 'mf_last_chapter_slug'], ['value' => $manga->source_id]);
                continue;
            }

            $chapters = $this->scraper->scrapeChapters($manga->source_id);

            if (empty($chapters)) {
                $this->warn("    No chapters found for {$manga->title}");
                Setting::updateOrCreate(['key' => 'mf_last_chapter_slug'], ['value' => $manga->source_id]);
                continue;
            }

            $chapterModels = [];
            foreach ($chapters as $ch) {
                $chapterModels[] = [
                    'number' => $ch['number'],
                    'title' => $ch['title'],
                ];
            }

            foreach ($chapterModels as $chData) {
                Chapter::create([
                    'manga_id' => $manga->id,
                    'number' => $chData['number'],
                    'title' => $chData['title'],
                ]);
            }

            $manga->update(['chapters_count' => count($chapterModels)]);
            $this->imported++;
            Setting::updateOrCreate(['key' => 'mf_last_chapter_slug'], ['value' => $manga->source_id]);
        }

        if ($done >= $total) {
            Setting::where('key', 'mf_last_chapter_slug')->delete();
        }

        $this->info("  Chapters phase done. Processed: {$done}");
    }
}
