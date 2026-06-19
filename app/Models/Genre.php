<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Genre extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'mal_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::saving(function ($genre) {

            if (empty($genre->slug)) {
                $slug = Str::slug($genre->name);

                // ✅ prevent duplicate slug
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
        Cache::forget('anime_genres_list');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function anime()
    {
        return $this->belongsToMany(Anime::class, 'anime_genre')
            ->withTimestamps();
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

    public function scopeWithAnimeCount($query)
    {
        return $query->withCount('anime');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function animeCount(): int
    {
        return $this->anime_count ?? $this->anime()->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Route Model Binding
    |--------------------------------------------------------------------------
    */

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
