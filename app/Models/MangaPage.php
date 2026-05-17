<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MangaPage extends Model
{
    protected $fillable = ['chapter_id', 'page_number', 'image_path'];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
