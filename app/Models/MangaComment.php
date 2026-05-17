<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MangaComment extends Model
{
    protected $fillable = ['chapter_id', 'user_id', 'body'];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
