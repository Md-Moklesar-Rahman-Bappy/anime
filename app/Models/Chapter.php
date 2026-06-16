<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Chapter extends Model
{
    protected $fillable = [
        'manga_id',
        'number',
        'title',
        'pages_count'
    ];

    protected function casts(): array
    {
        return [
            'number' => 'decimal:1',
            'pages_count' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
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

    public function manga(): BelongsTo
    {
        return $this->belongsTo(Manga::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(MangaPage::class)->orderBy('page_number');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(MangaComment::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(ChapterBookmark::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (VERY IMPORTANT)
    |--------------------------------------------------------------------------
    */

    public function scopeLatest($query)
    {
        return $query->orderByDesc('created_at');
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
    | Navigation helpers
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