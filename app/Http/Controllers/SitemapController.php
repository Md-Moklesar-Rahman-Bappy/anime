<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Manga;
use App\Models\MangaGenre;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SitemapController extends Controller
{
    const TTL = 1800;
    const LIMIT = 50000;

    public function index()
    {
        try {
            $urls = Cache::remember('sitemap_urls', self::TTL, function () {
                return $this->generateUrls();
            });

            return response()
                ->view('sitemap', compact('urls'))
                ->header('Content-Type', 'text/xml');

        } catch (\Throwable $e) {
            Log::error('Sitemap generation failed', [
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Failed to generate sitemap.');
        }
    }

    private function generateUrls(): array
    {
        $urls = [];

        // ✅ Static pages
        $urls = array_merge($urls, $this->staticPages());

        // ✅ Anime
        foreach (Anime::select('slug', 'updated_at')->cursor() as $anime) {
            $urls[] = $this->entry(
                route('anime.detail', $anime->slug),
                '0.9',
                'weekly',
                $anime->updated_at
            );
        }

        // ✅ Episodes (limit safe)
        foreach (
            Episode::with('anime:slug,id,updated_at')
                ->latest()
                ->limit(500)
                ->get()
                ->unique('anime_id') as $episode
        ) {
            if ($episode->anime) {
                $urls[] = $this->entry(
                    route('watch', ['slug' => $episode->anime->slug]),
                    '0.8',
                    'daily',
                    $episode->anime->updated_at
                );
            }
        }

        // ✅ Genres
        foreach (Genre::select('slug', 'updated_at')->cursor() as $genre) {
            $urls[] = $this->entry(
                route('genre', $genre->slug),
                '0.5',
                'weekly',
                $genre->updated_at
            );
        }

        // ✅ A-Z
        foreach (range('A', 'Z') as $letter) {
            $urls[] = $this->entry(
                route('az.list', $letter),
                '0.4',
                'weekly',
                now()
            );
        }

        // ✅ Manga
        foreach (Manga::select('slug', 'updated_at')->cursor() as $manga) {
            $urls[] = $this->entry(
                route('manga.detail', $manga->slug),
                '0.9',
                'weekly',
                $manga->updated_at
            );
        }

        // ✅ Manga Genres
        foreach (MangaGenre::select('slug', 'updated_at')->cursor() as $genre) {
            $urls[] = $this->entry(
                route('manga.genre', $genre->slug),
                '0.5',
                'weekly',
                $genre->updated_at
            );
        }

        // ✅ Limit protection
        return array_slice($this->unique($urls), 0, self::LIMIT);
    }

    private function staticPages(): array
    {
        return [
            $this->entry(route('home'), '1.0', 'hourly'),
            $this->entry(route('newest'), '0.8', 'daily'),
            $this->entry(route('updated'), '0.8', 'hourly'),
            $this->entry(route('ongoing'), '0.8', 'daily'),
        ];
    }

    private function entry($loc, $priority, $freq, $lastmod = null): array
    {
        return [
            'loc' => $loc,
            'priority' => $priority,
            'changefreq' => $freq,
            'lastmod' => $lastmod?->toIso8601String() ?? now()->toIso8601String(),
        ];
    }

    private function unique(array $urls): array
    {
        $seen = [];
        $result = [];

        foreach ($urls as $url) {
            if (!isset($seen[$url['loc']])) {
                $seen[$url['loc']] = true;
                $result[] = $url;
            }
        }

        return $result;
    }
}