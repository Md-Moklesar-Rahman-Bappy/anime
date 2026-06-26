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
        if ($this->thumbnail) {
            return str_starts_with($this->thumbnail, 'http') ? $this->thumbnail : Storage::url($this->thumbnail);
        }

        $letter = mb_substr($this->title ?? 'A', 0, 1);
        return 'data:image/svg+xml,' . rawurlencode("<svg xmlns='http://www.w3.org/2000/svg' width='300' height='420'><rect fill='%23374151' width='300' height='420'/><text x='150' y='210' text-anchor='middle' dominant-baseline='central' fill='white' font-size='80' font-family='sans-serif'>{$letter}</text></svg>");
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
