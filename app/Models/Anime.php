<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anime extends Model
{
    protected $table = 'anime';

    protected $fillable = [
        'mal_id', 'title', 'slug', 'description', 'type', 'status', 'country',
        'season', 'year', 'rating', 'score', 'episodes_count', 'duration',
        'source', 'studio', 'producers', 'licensors', 'thumbnail', 'banner',
        'views', 'featured', 'jikan_synced_at',
    ];

    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }

    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
