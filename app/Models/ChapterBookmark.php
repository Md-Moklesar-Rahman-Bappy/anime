<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class ChapterBookmark extends Model
{
    protected $fillable = [
        'user_id',
        'chapter_id',
        'page_number'
    ];

    protected function casts(): array
    {
        return [
            'page_number' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        // ✅ Default page
        static::creating(function ($bookmark) {
            if ($bookmark->page_number <= 0) {
                $bookmark->page_number = 1;
            }
        });

        // ✅ Cache invalidation
        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }

    protected static function clearCache(): void
    {
        Cache::forget('user_bookmarks');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (VERY IMPORTANT)
    |--------------------------------------------------------------------------
    */

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForChapter($query, int $chapterId)
    {
        return $query->where('chapter_id', $chapterId);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public static function getOrCreate(int $userId, int $chapterId): self
    {
        return static::firstOrCreate(
            [
                'user_id' => $userId,
                'chapter_id' => $chapterId,
            ],
            [
                'page_number' => 1,
            ]
        );
    }
}