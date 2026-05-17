<?php

namespace App\Providers;

use App\Services\Scrapers\AnimePaheScraper;
use App\Services\Scrapers\GogoanimeByScraper;
use App\Services\Scrapers\GogoanimeScraper;
use App\Services\Scrapers\ScraperManager;
use App\Services\Scrapers\ZoroScraper;
use App\Services\Scrapers\ZoroTvScraper;
use Illuminate\Support\ServiceProvider;

class ScraperServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ScraperManager::class, function () {
            $manager = new ScraperManager;
            $manager->register(new GogoanimeScraper);
            $manager->register(new ZoroScraper);
            $manager->register(new AnimePaheScraper);
            $manager->register(new ZoroTvScraper);
            $manager->register(new GogoanimeByScraper);

            return $manager;
        });
    }

    public function boot(): void
    {
        //
    }
}
