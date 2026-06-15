<?php

namespace App\Models;

use App\Services\AssetUrlService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Anime extends Model
{
    protected $table = 'anime';

    protected $fillable = [
        'mal_id', 'title', 'slug', 'description', 'type', 'status', 'country',
        'season', 'year', 'rating', 'score', 'age_rating', 'episodes_count',
        'duration', 'source', 'studio', 'producers', 'licensors',
        'thumbnail', 'banner', 'views', 'featured', 'featured_order', 'jikan_synced_at',
    ];

    protected $appends = ['thumbnail_url', 'banner_url'];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'rating' => 'decimal:1',
            'score' => 'decimal:2',
            'episodes_count' => 'integer',
            'duration' => 'integer',
            'views' => 'integer',
            'featured' => 'boolean',
            'featured_order' => 'integer',
            'jikan_synced_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function ($anime) {
            // ✅ Auto slug generation
            if (empty($anime->slug)) {
                $anime->slug = Str::slug($anime->title) . '-' . uniqid();
            }
        });

        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }

    protected static function clearCache(): void
    {
        // ✅ Clear important caches
        Cache::forget('home_all');
        Cache::forget('home_featured');
        Cache::forget('anime_genres_list');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getThumbnailUrlAttribute(): string
    {
        return app(AssetUrlService::class)
            ->thumbnailUrl($this->thumbnail, $this->title ?? 'A');
    }

    public function getBannerUrlAttribute(): string
    {
        return app(AssetUrlService::class)
            ->bannerUrl($this->banner, $this->thumbnail_url);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (VERY USEFUL)
    |--------------------------------------------------------------------------
    */

    public function scopeTrending($query)
    {
        return $query->orderBy('views', 'desc');
    }

    public function scopeOngoing($query)
    {
        return $query->where('status', 'Ongoing');
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true)
                     ->orderBy('featured_order');
    }

    public function scopeLatest($query)
    {
        return $query->orderByDesc('created_at');
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