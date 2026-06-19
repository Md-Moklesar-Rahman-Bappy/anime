<?php

namespace App\Models;

use App\Services\AssetUrlService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Anime extends Model
{
    protected $table = 'anime';

    protected $fillable = [
        'mal_id','title','slug','description','type','status','country',
        'season','year','rating','score','age_rating','episodes_count',
        'duration','source','studio','producers','licensors',
        'thumbnail','banner','views','featured','featured_order','jikan_synced_at',
    ];

    protected $appends = ['thumbnail_url', 'banner_url'];

    protected $casts = [
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

    protected static function booted(): void
    {
        static::saving(function ($anime) {
            if (empty($anime->slug)) {
                $slug = Str::slug($anime->title);

                if (self::where('slug', $slug)->exists()) {
                    $slug .= '-' . uniqid();
                }

                $anime->slug = $slug;
            }
        });

        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }

    protected static function clearCache(): void
    {
        Cache::forget('home_all');
        Cache::forget('home_featured');
    }

    public function getThumbnailUrlAttribute(): string
    {
        return app(AssetUrlService::class)->thumbnailUrl($this->thumbnail, $this->title);
    }

    public function getBannerUrlAttribute(): string
    {
        return app(AssetUrlService::class)->bannerUrl($this->banner, $this->thumbnail);
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'anime_genre');
    }

    public function episodes()
    {
        return $this->hasMany(Episode::class)->orderBy('number');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}