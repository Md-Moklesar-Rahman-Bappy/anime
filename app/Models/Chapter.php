<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Chapter extends Model
{
    protected $fillable = [
        'manga_id',
        'number',
        'title',
        'pages_count',
    ];

    protected $casts = [
        'number' => 'decimal:1',   // supports 1.5, 2.5 etc
        'pages_count' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::saved(fn() => static::clearCache());
        static::deleted(fn() => static::clearCache());
    }

    protected static function clearCache(): void
    {
        Cache::forget('manga_home_recent_chapters');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function manga()
    {
        return $this->belongsTo(Manga::class);
    }

    public function pages()
    {
        return $this->hasMany(MangaPage::class)
            ->orderBy('page_number');
    }

    public function comments()
    {
        return $this->hasMany(MangaComment::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(ChapterBookmark::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeLatest($query)
    {
        return $query->latest();
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('number');
    }

    public function scopeForManga($query, int $mangaId)
    {
        return $query->where('manga_id', $mangaId);
    }

    /*
    |--------------------------------------------------------------------------
    | Navigation (Next / Previous)
    |--------------------------------------------------------------------------
    */

    public function previous()
    {
        return static::where('manga_id', $this->manga_id)
            ->where('number', '<', $this->number)
            ->orderByDesc('number')
            ->first();
    }

    public function next()
    {
        return static::where('manga_id', $this->manga_id)
            ->where('number', '>', $this->number)
            ->orderBy('number')
            ->first();
    }
}
