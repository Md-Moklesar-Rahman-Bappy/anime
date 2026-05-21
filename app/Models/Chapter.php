<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    protected $fillable = ['manga_id', 'number', 'title', 'pages_count'];

    protected function casts(): array
    {
        return [
            'number' => 'decimal:1',
            'pages_count' => 'integer',
        ];
    }

    public function manga(): BelongsTo
    {
        return $this->belongsTo(Manga::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(MangaPage::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(MangaComment::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(ChapterBookmark::class);
    }
}
