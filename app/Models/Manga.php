<?php

namespace App\Models;

use App\Services\AssetUrlService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Manga extends Model
{
    protected $table = 'manga';

    protected $fillable = [
        'title','slug','description','alternative_titles',
        'type','status','year','rating','score',
        'chapters_count','source','source_id',
        'author','artist','publisher',
        'thumbnail','banner','views','featured','featured_order',
    ];

    protected $appends = ['thumbnail_url', 'banner_url'];

    protected $casts = [
        'year' => 'integer',
        'rating' => 'decimal:1',
        'score' => 'decimal:2',
        'chapters_count' => 'integer',
        'views' => 'integer',
        'featured' => 'boolean',
        'featured_order' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::saving(function ($manga) {

            // ✅ safe slug generation
            if (empty($manga->slug)) {
                $slug = Str::slug($manga->title);

                if (self::where('slug', $slug)->exists()) {
                    $slug .= '-' . uniqid();
                }

                $manga->slug = $slug;
            }

            // ✅ fallback featured order
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
            ->bannerUrl($this->banner, $this->thumbnail);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function genres()
    {
        return $this->belongsToMany(
            MangaGenre::class,
            'manga_genre_relation',
            'manga_id',
            'manga_genre_id'
        )->withTimestamps();
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class)
                    ->orderByDesc('number');
    }

    public function favorites()
    {
        return $this->hasMany(MangaFavorite::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'manga_favorites');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeLatest($query)
    {
        return $query->latest();
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
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function latestChapter()
    {
        return $this->chapters()->first();
    }

    public function hasChapters(): bool
    {
        return $this->chapters()->exists();
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