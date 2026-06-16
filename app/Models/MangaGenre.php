<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MangaGenre extends Model
{
    protected $fillable = ['name', 'slug'];

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::saving(function ($genre) {
            // ✅ Auto slug generation
            if (empty($genre->slug)) {
                $genre->slug = Str::slug($genre->name);
            }
        });

        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }

    protected static function clearCache(): void
    {
        Cache::forget('manga_genres_list');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function manga(): BelongsToMany
    {
        return $this->belongsToMany(
            Manga::class,
            'manga_genre_relation',
            'manga_genre_id',
            'manga_id'
        )->withTimestamps(); // ✅ improved tracking
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (VERY IMPORTANT)
    |--------------------------------------------------------------------------
    */

    public function scopeAlphabetical($query)
    {
        return $query->orderBy('name');
    }

    public function scopeWithMangaCount($query)
    {
        return $query->withCount('manga');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function mangaCount(): int
    {
        return $this->manga()->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Route model binding
    |--------------------------------------------------------------------------
    */

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}