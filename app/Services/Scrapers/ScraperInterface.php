<?php

namespace App\Services\Scrapers;

interface ScraperInterface
{
    public function name(): string;

    public function search(string $query): array;

    public function getEpisodes(string $animeId): array;

    public function getVideoUrl(string $episodeId): ?string;
}
