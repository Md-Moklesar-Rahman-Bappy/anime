<?php

namespace App\Services\Scrapers;

class ScraperManager
{
    protected array $scrapers = [];

    public function register(ScraperInterface $scraper): void
    {
        $this->scrapers[get_class($scraper)] = $scraper;
    }

    public function all(): array
    {
        return $this->scrapers;
    }

    public function get(string $class): ?ScraperInterface
    {
        return $this->scrapers[$class] ?? null;
    }

    public function names(): array
    {
        return array_map(fn ($s) => [
            'class' => get_class($s),
            'name' => $s->name(),
        ], $this->scrapers);
    }
}
