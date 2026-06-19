<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Favorite extends Model
{
    public const CATEGORY_WATCHING      = 'watching';
    public const CATEGORY_COMPLETED     = 'completed';
    public const CATEGORY_PLAN          = 'plan_to_watch';
    public const CATEGORY_ON_HOLD       = 'on_hold';
    public const CATEGORY_DROPPED       = 'dropped';

    public const CATEGORIES = [
        self::CATEGORY_WATCHING,
        self::CATEGORY_COMPLETED,
        self::CATEGORY_PLAN,
        self::CATEGORY_ON_HOLD,
        self::CATEGORY_DROPPED,
    ];

    protected $fillable = [
        'user_id',
        'anime_id',
        'category',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'anime_id' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function ($favorite) {

            // ✅ default category
            if (empty($favorite->category)) {
                $favorite->category = self::CATEGORY_PLAN;
            }

            // ✅ normalize category
            $favorite->category = strtolower($favorite->category);
        });

        static::saved(fn() => static::clearCache());
        static::deleted(fn() => static::clearCache());
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function anime()
    {
        return $this->belongsTo(Anime::class);
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

    public function scopeForAnime($query, int $animeId)
    {
        return $query->where('anime_id', $animeId);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', strtolower($category));
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (VERY IMPORTANT)
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
            'category' => self::CATEGORY_PLAN,
        ]);

        return 'added';
    }

    public static function setCategory(int $userId, int $animeId, string $category): self
    {
        return static::updateOrCreate(
            [
                'user_id' => $userId,
                'anime_id' => $animeId,
            ],
            [
                'category' => strtolower($category),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Category Helpers
    |--------------------------------------------------------------------------
    */

    public function isWatching(): bool
    {
        return $this->category === self::CATEGORY_WATCHING;
    }

    public function isCompleted(): bool
    {
        return $this->category === self::CATEGORY_COMPLETED;
    }

    public function isPlan(): bool
    {
        return $this->category === self::CATEGORY_PLAN;
    }
}
