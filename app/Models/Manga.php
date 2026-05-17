<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manga extends Model
{
    protected $table = 'manga';

    protected $fillable = [
        'title', 'slug', 'description', 'alternative_titles',
        'type', 'status', 'year', 'rating', 'score',
        'chapters_count', 'source', 'source_id', 'author', 'artist', 'publisher',
        'thumbnail', 'banner', 'views', 'featured', 'featured_order',
    ];

    public function genres()
    {
        return $this->belongsToMany(MangaGenre::class, 'manga_genre_relation', 'manga_id', 'manga_genre_id');
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    public function favorites()
    {
        return $this->hasMany(MangaFavorite::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'manga_favorites');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
