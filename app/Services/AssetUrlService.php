<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssetUrlService
{
    /*
    |--------------------------------------------------------------------------
    | RESOLVE ASSET URL
    |--------------------------------------------------------------------------
    */
    public function resolve(?string $path, ?string $fallback = null, ?string $disk = null): ?string
    {
        if (!$path) {
            return $fallback;
        }

        // ✅ External URL support
        if ($this->isExternal($path)) {
            return $path;
        }

        try {
            return $disk
                ? Storage::disk($disk)->url($path)
                : Storage::url($path);

        } catch (\Throwable $e) {
            return $fallback;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | THUMBNAIL URL
    |--------------------------------------------------------------------------
    */
    public function thumbnailUrl(?string $thumbnail, string $title): string
    {
        return $thumbnail
            ? $this->resolve($thumbnail)
            : $this->placeholder($title, 300, 420);
    }

    /*
    |--------------------------------------------------------------------------
    | BANNER URL
    |--------------------------------------------------------------------------
    */
    public function bannerUrl(?string $banner, ?string $fallbackThumbnail = null): string
    {
        return $this->resolve($banner)
            ?? $fallbackThumbnail
            ?? '';
    }

    /*
    |--------------------------------------------------------------------------
    | PLACEHOLDER GENERATOR
    |--------------------------------------------------------------------------
    */
    protected function placeholder(string $text, int $width, int $height): string
    {
        $text = trim($text);

        // ✅ fallback if empty
        $letter = $text !== ''
            ? strtoupper(Str::substr($text, 0, 1))
            : '?';

        // ✅ sanitize
        $letter = htmlspecialchars($letter, ENT_QUOTES, 'UTF-8');

        $svg = "
        <svg xmlns='http://www.w3.org/2000/svg' width='{$width}' height='{$height}'>
            <rect fill='#374151' width='{$width}' height='{$height}'/>
            <text x='50%' y='50%' text-anchor='middle' dominant-baseline='central'
                fill='white' font-size='80' font-family='sans-serif'>
                {$letter}
            </text>
        </svg>";

        return 'data:image/svg+xml,' . rawurlencode($svg);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */
    protected function isExternal(string $path): bool
    {
        return str_starts_with($path, 'http://') ||
            str_starts_with($path, 'https://');
    }
}
