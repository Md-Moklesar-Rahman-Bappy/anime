<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Anime extends Model
{
    protected $table = 'anime';

    protected $fillable = [
        'mal_id', 'title', 'slug', 'description', 'type', 'status', 'country',
        'season', 'year', 'rating', 'score', 'age_rating', 'episodes_count', 'duration',
        'source', 'studio', 'producers', 'licensors', 'thumbnail', 'banner',
        'views', 'featured', 'featured_order', 'jikan_synced_at',
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

    public function getThumbnailUrlAttribute(): string
    {
        return $this->thumbnail
            ? (str_starts_with($this->thumbnail, 'http') ? $this->thumbnail : Storage::url($this->thumbnail))
            : 'https://via.placeholder.com/300x420/1a1a2e/7c3aed?text='.urlencode($this->title ?? 'Anime');
    }

    public function getBannerUrlAttribute(): string
    {
        return $this->banner
            ? (str_starts_with($this->banner, 'http') ? $this->banner : Storage::url($this->banner))
            : $this->thumbnail_url;
    }

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

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
