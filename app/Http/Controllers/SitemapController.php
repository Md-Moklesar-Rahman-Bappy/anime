<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Manga;
use App\Models\MangaGenre;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = Cache::remember('sitemap_urls', 1800, function () {
            return $this->generateUrls();
        });

        return response()->view('sitemap', compact('urls'))->header('Content-Type', 'text/xml');
    }

    private function generateUrls(): array
    {
        $urls = [];

        $baseUrl = config('app.url');

        // Static pages (priority: 1.0 - 0.6)
        $staticPages = [
            ['loc' => $baseUrl . '/', 'priority' => '1.0', 'changefreq' => 'hourly'],
            ['loc' => $baseUrl . '/newest', 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => $baseUrl . '/updated', 'priority' => '0.8', 'changefreq' => 'hourly'],
            ['loc' => $baseUrl . '/ongoing', 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => $baseUrl . '/trending', 'priority' => '0.7', 'changefreq' => 'daily'],
            ['loc' => $baseUrl . '/az-list', 'priority' => '0.6', 'changefreq' => 'weekly'],
            ['loc' => $baseUrl . '/filter', 'priority' => '0.5', 'changefreq' => 'weekly'],
            ['loc' => $baseUrl . '/manga', 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => $baseUrl . '/manga/newest', 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => $baseUrl . '/manga/updated', 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => $baseUrl . '/manga/ongoing', 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => $baseUrl . '/manga/trending', 'priority' => '0.7', 'changefreq' => 'daily'],
            ['loc' => $baseUrl . '/manga/completed', 'priority' => '0.7', 'changefreq' => 'daily'],
            ['loc' => $baseUrl . '/manga/filter', 'priority' => '0.5', 'changefreq' => 'weekly'],
            ['loc' => $baseUrl . '/faq', 'priority' => '0.4', 'changefreq' => 'monthly'],
            ['loc' => $baseUrl . '/about', 'priority' => '0.4', 'changefreq' => 'monthly'],
            ['loc' => $baseUrl . '/contact', 'priority' => '0.4', 'changefreq' => 'monthly'],
            ['loc' => $baseUrl . '/dmca', 'priority' => '0.3', 'changefreq' => 'monthly'],
            ['loc' => $baseUrl . '/terms', 'priority' => '0.3', 'changefreq' => 'monthly'],
        ];

        foreach ($staticPages as $page) {
            $urls[] = $this->makeEntry($page['loc'], $page['priority'], $page['changefreq'], now());
        }

        // Anime detail pages
        $animes = Anime::select('slug', 'updated_at')->orderBy('updated_at', 'desc')->cursor();
        foreach ($animes as $anime) {
            $urls[] = $this->makeEntry($baseUrl . '/anime/' . $anime->slug, '0.9', 'weekly', $anime->updated_at);
        }

        // Watch pages (most recent episodes)
        $episodes = Episode::select('anime_id')
            ->with('anime:slug,id,updated_at')
            ->latest()
            ->limit(500)
            ->get()
            ->unique('anime_id');

        foreach ($episodes as $episode) {
            if ($episode->anime) {
                $urls[] = $this->makeEntry($baseUrl . '/watch/' . $episode->anime->slug, '0.8', 'daily', $episode->anime->updated_at);
            }
        }

        // Genre pages
        $genres = Genre::select('slug', 'updated_at')->cursor();
        foreach ($genres as $genre) {
            $urls[] = $this->makeEntry($baseUrl . '/genre/' . $genre->slug, '0.5', 'weekly', $genre->updated_at);
        }

        // A-Z list letter pages
        foreach (range('A', 'Z') as $letter) {
            $urls[] = $this->makeEntry($baseUrl . '/az-list/' . $letter, '0.4', 'weekly', now());
        }

        // Manga detail pages
        $mangaList = Manga::select('slug', 'updated_at')->orderBy('updated_at', 'desc')->cursor();
        foreach ($mangaList as $manga) {
            $urls[] = $this->makeEntry($baseUrl . '/manga/' . $manga->slug, '0.9', 'weekly', $manga->updated_at);
        }

        // Manga genre pages
        $mangaGenres = MangaGenre::select('slug', 'updated_at')->cursor();
        foreach ($mangaGenres as $genre) {
            $urls[] = $this->makeEntry($baseUrl . '/manga/genre/' . $genre->slug, '0.5', 'weekly', $genre->updated_at);
        }

        // Remove duplicates by loc
        $seen = [];
        $unique = [];
        foreach ($urls as $url) {
            $key = $url['loc'];
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $url;
            }
        }

        return $unique;
    }

    private function makeEntry(string $loc, string $priority, string $changefreq, $lastmod): array
    {
        return [
            'loc' => $loc,
            'priority' => $priority,
            'changefreq' => $changefreq,
            'lastmod' => $lastmod instanceof \DateTimeInterface ? $lastmod->toIso8601String() : now()->toIso8601String(),
        ];
    }
}
