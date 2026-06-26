<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Genre extends Model
{
    protected $fillable = ['name', 'slug', 'mal_id'];

    public function anime(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
