<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Favorite extends Model
{
    public const CATEGORIES = [
        'watching',
        'completed',
        'plan_to_watch',
        'on_hold',
        'dropped',
    ];

    protected $fillable = [
        'user_id',
        'anime_id',
        'category',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'anime_id' => 'integer',
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
                $favorite->category = 'plan_to_watch';
            }
        });

        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }

    protected static function clearCache(): void
    {
        Cache::forget('user_favorites');
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

    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
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

    public function scopeForAnime($query, int $animeId)
    {
        return $query->where('anime_id', $animeId);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (VERY USEFUL)
    |--------------------------------------------------------------------------
    */

    public static function toggle(int $userId, int $animeId): string
    {
        $favorite = static::where('user_id', $userId)
            ->where('anime_id', $animeId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return 'removed';
        }

        static::create([
            'user_id' => $userId,
            'anime_id' => $animeId,
        ]);

        return 'added';
    }

    public function isWatching(): bool
    {
        return $this->category === 'watching';
    }

    public function isCompleted(): bool
    {
        return $this->category === 'completed';
    }
}
