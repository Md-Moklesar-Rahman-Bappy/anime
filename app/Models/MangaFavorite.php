<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class MangaFavorite extends Model
{
    public const CATEGORIES = [
        'reading',
        'completed',
        'plan_to_read',
        'on_hold',
        'dropped',
    ];

    protected $fillable = [
        'user_id',
        'manga_id',
        'category',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'manga_id' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function ($favorite) {
            if (empty($favorite->category)) {
                $favorite->category = 'plan_to_read';
            }
        });

        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }

    protected static function clearCache(): void
    {
        Cache::forget('user_manga_favorites');
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

    public function manga(): BelongsTo
    {
        return $this->belongsTo(Manga::class);
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

    public function scopeForManga($query, int $mangaId)
    {
        return $query->where('manga_id', $mangaId);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (VERY IMPORTANT)
    |--------------------------------------------------------------------------
    */

    public static function toggle(int $userId, int $mangaId): string
    {
        $favorite = static::where('user_id', $userId)
            ->where('manga_id', $mangaId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return 'removed';
        }

        static::create([
            'user_id' => $userId,
            'manga_id' => $mangaId,
        ]);

        return 'added';
    }

    public function isReading(): bool
    {
        return $this->category === 'reading';
    }

    public function isCompleted(): bool
    {
        return $this->category === 'completed';
    }
}