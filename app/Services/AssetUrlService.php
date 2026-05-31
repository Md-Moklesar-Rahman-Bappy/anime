<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class AssetUrlService
{
    public function resolve(?string $path, ?string $fallback = null): ?string
    {
        if (!$path) {
            return $fallback;
        }

        return str_starts_with($path, 'http') ? $path : Storage::url($path);
    }

    public function thumbnailUrl(?string $thumbnail, string $title): string
    {
        if ($thumbnail) {
            return $this->resolve($thumbnail);
        }

        $letter = mb_substr($title, 0, 1);

        return 'data:image/svg+xml,' . rawurlencode(
            "<svg xmlns='http://www.w3.org/2000/svg' width='300' height='420'>" .
            "<rect fill='%23374151' width='300' height='420'/>" .
            "<text x='150' y='210' text-anchor='middle' dominant-baseline='central' fill='white' font-size='80' font-family='sans-serif'>{$letter}</text></svg>"
        );
    }

    public function bannerUrl(?string $banner, ?string $fallbackThumbnail = null): string
    {
        return $this->resolve($banner) ?? $fallbackThumbnail ?? '';
    }
}
