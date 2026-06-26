<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MangaGenre extends Model
{
    protected $fillable = ['name', 'slug'];

    public function manga(): BelongsToMany
    {
        return $this->belongsToMany(Manga::class, 'manga_genre_relation', 'manga_genre_id', 'manga_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
