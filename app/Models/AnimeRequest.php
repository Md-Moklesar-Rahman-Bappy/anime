<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnimeRequest extends Model
{
    protected $table = 'requests';

    protected $fillable = ['user_id', 'anime_title', 'description', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
