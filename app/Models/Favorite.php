<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    protected $fillable = ['user_id', 'anime_id', 'category'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }
}
