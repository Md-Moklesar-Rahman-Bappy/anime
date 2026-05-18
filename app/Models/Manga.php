<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Manga extends Model
{
    protected $table = 'manga';

    protected $fillable = [
        'title', 'slug', 'description', 'alternative_titles',
        'type', 'status', 'year', 'rating', 'score',
        'chapters_count', 'source', 'source_id', 'author', 'artist', 'publisher',
        'thumbnail', 'banner', 'views', 'featured', 'featured_order',
    ];

    protected function casts(): array
    {
        return [
            'alternative_titles' => 'array',
            'year' => 'integer',
            'rating' => 'decimal:1',
            'score' => 'decimal:2',
            'chapters_count' => 'integer',
            'views' => 'integer',
            'featured' => 'boolean',
            'featured_order' => 'integer',
        ];
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
