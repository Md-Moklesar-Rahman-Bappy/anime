<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Manga extends Model
{
    protected $table = 'manga';

    protected $fillable = [
        'title', 'slug', 'description', 'alternative_titles',
        'type', 'status', 'year', 'rating', 'score',
        'chapters_count', 'source', 'source_id', 'author', 'artist', 'publisher',
        'thumbnail', 'banner', 'views', 'featured', 'featured_order',
    ];

    protected $appends = ['thumbnail_url', 'banner_url'];

    protected function casts(): array
    {
        return [
            'alternative_titles' => 'string',
            'year' => 'integer',
            'rating' => 'decimal:1',
            'score' => 'decimal:2',
            'chapters_count' => 'integer',
            'views' => 'integer',
            'featured' => 'boolean',
            'featured_order' => 'integer',
        ];
    }

    public function getThumbnailUrlAttribute(): string
    {
        return $this->thumbnail
            ? (str_starts_with($this->thumbnail, 'http') ? $this->thumbnail : Storage::url($this->thumbnail))
            : 'https://via.placeholder.com/300x420/1a1a2e/7c3aed?text='.urlencode($this->title ?? 'Manga');
    }

    public function getBannerUrlAttribute(): string
    {
        return $this->banner
            ? (str_starts_with($this->banner, 'http') ? $this->banner : Storage::url($this->banner))
            : $this->thumbnail_url;
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(MangaGenre::class, 'manga_genre_relation', 'manga_id', 'manga_genre_id');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(MangaFavorite::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'manga_favorites');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
