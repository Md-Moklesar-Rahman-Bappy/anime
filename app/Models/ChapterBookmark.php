<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ChapterBookmark extends Model
{
    protected $fillable = [
        'user_id',
        'chapter_id',
        'page_number',
    ];

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
        static::creating(function ($bookmark) {

            // ✅ ensure valid page number
            if (empty($bookmark->page_number) || $bookmark->page_number <= 0) {
                $bookmark->page_number = 1;
            }
        });

        static::saved(fn() => static::clearCache());
        static::deleted(fn() => static::clearCache());
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
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
    | Helpers (VERY IMPORTANT)
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

    public function updatePage(int $page): void
    {
        $this->update([
            'page_number' => max(1, $page),
        ]);
    }
}
