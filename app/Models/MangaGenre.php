<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MangaGenre extends Model
{
    protected $fillable = [
        'name',
        'slug'
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::saving(function ($genre) {

            // ✅ Safe unique slug
            if (empty($genre->slug)) {
                $slug = Str::slug($genre->name);

                if (self::where('slug', $slug)->exists()) {
                    $slug .= '-' . uniqid();
                }

                $genre->slug = $slug;
            }
        });

        static::saved(fn() => static::clearCache());
        static::deleted(fn() => static::clearCache());
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

    public function manga()
    {
        return $this->belongsToMany(
            Manga::class,
            'manga_genre_relation',
            'manga_genre_id',
            'manga_id'
        )->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
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
        // ✅ use withCount if loaded (performance)
        return $this->manga_count ?? $this->manga()->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Route binding
    |--------------------------------------------------------------------------
    */

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
