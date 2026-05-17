<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MangaGenre extends Model
{
    protected $fillable = ['name', 'slug'];

    public function manga()
    {
        return $this->belongsToMany(Manga::class, 'manga_genre_relation', 'manga_genre_id', 'manga_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
