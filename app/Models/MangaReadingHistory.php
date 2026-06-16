<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class MangaReadingHistory extends Model
{
    protected $table = 'manga_reading_history';

    protected $fillable = [
        'user_id',
        'chapter_id',
        'page_number',
        'completed',
    ];

    protected function casts(): array
    {
        return [
            'page_number' => 'integer',
            'completed' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function ($history) {
            if ($history->page_number <= 0) {
                $history->page_number = 1;
            }

            if ($history->completed === null) {
                $history->completed = false;
            }
        });

        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }

    protected static function clearCache(): void
    {
        Cache::forget('user_reading_history');
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
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    public function scopeInProgress($query)
    {
        return $query->where('completed', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (VERY IMPORTANT)
    |--------------------------------------------------------------------------
    */

    public function progressPercentage(): float
    {
        $totalPages = $this->chapter?->pages()->count() ?? 0;

        if ($totalPages === 0) {
            return 0;
        }

        return round(($this->page_number / $totalPages) * 100, 2);
    }

    public function markCompleted(): void
    {
        $this->update(['completed' => true]);
    }

    public static function updateProgress(int $userId, int $chapterId, int $page): self
    {
        return static::updateOrCreate(
            [
                'user_id' => $userId,
                'chapter_id' => $chapterId,
            ],
            [
                'page_number' => $page,
                'completed' => false,
            ]
        );
    }
}
