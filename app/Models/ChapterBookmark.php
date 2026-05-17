<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChapterBookmark extends Model
{
    protected $fillable = ['user_id', 'chapter_id', 'page_number'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
