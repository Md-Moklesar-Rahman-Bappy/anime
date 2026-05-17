<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Scrapers\ScraperManager;
use App\Services\Scrapers\GogoanimeScraper;
use App\Services\Scrapers\ZoroScraper;
use App\Services\Scrapers\AnimePaheScraper;

class ScraperServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ScraperManager::class, function () {
            $manager = new ScraperManager();
            $manager->register(new GogoanimeScraper());
            $manager->register(new ZoroScraper());
            $manager->register(new AnimePaheScraper());
            return $manager;
        });
    }

    public function boot(): void
    {
        //
    }
}
