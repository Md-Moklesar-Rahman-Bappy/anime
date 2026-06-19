<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class MangaPage extends Model
{
    protected $fillable = [
        'chapter_id',
        'page_number',
        'image_path',
    ];

    protected $appends = ['image_url'];

    protected $casts = [
        'page_number' => 'integer',
    ];

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
        Cache::forget('chapter_pages');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getImageUrlAttribute(): string
    {
        if (!$this->image_path) {
            return '';
        }

        return str_starts_with($this->image_path, 'http')
            ? $this->image_path
            : Storage::disk('public')->url($this->image_path);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForChapter($query, int $chapterId)
    {
        return $query->where('chapter_id', $chapterId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('page_number');
    }

    /*
    |--------------------------------------------------------------------------
    | Navigation (Reader logic)
    |--------------------------------------------------------------------------
    */

    public function next()
    {
        return static::where('chapter_id', $this->chapter_id)
            ->where('page_number', '>', $this->page_number)
            ->orderBy('page_number')
            ->first();
    }

    public function previous()
    {
        return static::where('chapter_id', $this->chapter_id)
            ->where('page_number', '<', $this->page_number)
            ->orderByDesc('page_number')
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (NEW)
    |--------------------------------------------------------------------------
    */

    public function isFirst(): bool
    {
        return $this->page_number === 1;
    }

    public function isLast(): bool
    {
        return ! static::where('chapter_id', $this->chapter_id)
            ->where('page_number', '>', $this->page_number)
            ->exists();
    }
}