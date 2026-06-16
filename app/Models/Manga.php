<?php

namespace App\Models;

use App\Services\AssetUrlService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Manga extends Model
{
    protected $table = 'manga';

    protected $fillable = [
        'title', 'slug', 'description', 'alternative_titles',
        'type', 'status', 'year', 'rating', 'score',
        'chapters_count', 'source', 'source_id',
        'author', 'artist', 'publisher',
        'thumbnail', 'banner', 'views', 'featured', 'featured_order',
    ];

    protected $appends = ['thumbnail_url', 'banner_url'];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'rating' => 'decimal:1',
            'score' => 'decimal:1',
            'chapters_count' => 'integer',
            'views' => 'integer',
            'featured' => 'boolean',
            'featured_order' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::saving(function ($manga) {
            // ✅ Auto slug generation
            if (empty($manga->slug)) {
                $manga->slug = Str::slug($manga->title) . '-' . uniqid();
            }

            if ($manga->featured_order === null) {
                $manga->featured_order = 0;
            }
        });

        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }

    protected static function clearCache(): void
    {
        Cache::forget('manga_home_all');
        Cache::forget('manga_home_recent_chapters');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getThumbnailUrlAttribute(): string
    {
        return app(AssetUrlService::class)
            ->thumbnailUrl($this->thumbnail, $this->title ?? 'Manga');
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
        return $this->belongsToMany(
            MangaGenre::class,
            'manga_genre_relation',
            'manga_id',
            'manga_genre_id'
        )->withTimestamps();
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('number', 'desc');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(MangaFavorite::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'manga_favorites');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (VERY IMPORTANT)
    |--------------------------------------------------------------------------
    */

    public function scopeLatest($query)
    {
        return $query->orderByDesc('created_at');
    }

    public function scopeTrending($query)
    {
        return $query->orderByDesc('views');
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true)
                     ->orderBy('featured_order');
    }

    public function scopeOngoing($query)
    {
        return $query->where('status', 'Ongoing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'Completed');
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