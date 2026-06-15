<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AssetUrlService
{
    /*
    |--------------------------------------------------------------------------
    | Resolve asset URL
    |--------------------------------------------------------------------------
    */

    public function resolve(?string $path, ?string $fallback = null, string $disk = null): ?string
    {
        if (!$path) {
            return $fallback;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return $disk
            ? Storage::disk($disk)->url($path)
            : Storage::url($path);
    }

    /*
    |--------------------------------------------------------------------------
    | Thumbnail
    |--------------------------------------------------------------------------
    */

    public function thumbnailUrl(?string $thumbnail, string $title): string
    {
        if ($thumbnail) {
            return $this->resolve($thumbnail);
        }

        return $this->placeholder($title, 300, 420);
    }

    /*
    |--------------------------------------------------------------------------
    | Banner
    |--------------------------------------------------------------------------
    */

    public function bannerUrl(?string $banner, ?string $fallbackThumbnail = null): string
    {
        return $this->resolve($banner) ?? $fallbackThumbnail ?? '';
    }

    /*
    |--------------------------------------------------------------------------
    | Placeholder generator (REUSABLE)
    |--------------------------------------------------------------------------
    */

    protected function placeholder(string $text, int $width, int $height): string
    {
        $letter = strtoupper(Str::substr(trim($text), 0, 1));

        // ✅ sanitize input
        $letter = htmlspecialchars($letter, ENT_QUOTES, 'UTF-8');

        return 'data:image/svg+xml,' . rawurlencode(
            "<svg xmlns='http://www.w3.org/2000/svg' width='{$width}' height='{$height}'>" .
            "<rect fill='#374151' width='{$width}' height='{$height}'/>" .
            "<text x='50%' y='50%' text-anchor='middle' dominant-baseline='central' fill='white' font-size='80' font-family='sans-serif'>{$letter}</text>" .
            "</svg>"
        );
    }
}
