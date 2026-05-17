<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $fillable = ['manga_id', 'number', 'title', 'pages_count'];

    public function manga()
    {
        return $this->belongsTo(Manga::class);
    }

    public function pages()
    {
        return $this->hasMany(MangaPage::class);
    }

    public function comments()
    {
        return $this->hasMany(MangaComment::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(ChapterBookmark::class);
    }
}
