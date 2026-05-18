<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anime extends Model
{
    protected $table = 'anime';

    protected $fillable = [
        'mal_id', 'title', 'slug', 'description', 'type', 'status', 'country',
        'season', 'year', 'rating', 'score', 'episodes_count', 'duration',
        'source', 'studio', 'producers', 'licensors', 'thumbnail', 'banner',
        'views', 'featured', 'jikan_synced_at',
    ];

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
            'jikan_synced_at' => 'datetime',
        ];
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
