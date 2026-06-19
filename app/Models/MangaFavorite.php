<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MangaFavorite extends Model
{
    public const CATEGORY_READING   = 'reading';
    public const CATEGORY_COMPLETED = 'completed';
    public const CATEGORY_PLAN      = 'plan_to_read';
    public const CATEGORY_ON_HOLD   = 'on_hold';
    public const CATEGORY_DROPPED   = 'dropped';

    public const CATEGORIES = [
        self::CATEGORY_READING,
        self::CATEGORY_COMPLETED,
        self::CATEGORY_PLAN,
        self::CATEGORY_ON_HOLD,
        self::CATEGORY_DROPPED,
    ];

    protected $fillable = [
        'user_id',
        'manga_id',
        'category',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'manga_id' => 'integer',
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
        Cache::forget('user_manga_favorites');
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

    public function manga()
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
        return $query->where('category', strtolower($category));
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
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
            'category' => self::CATEGORY_PLAN,
        ]);

        return 'added';
    }

    public static function setCategory(int $userId, int $mangaId, string $category): self
    {
        return static::updateOrCreate(
            [
                'user_id' => $userId,
                'manga_id' => $mangaId,
            ],
            [
                'category' => strtolower($category),
            ]
        );
    }

    public function isReading(): bool
    {
        return $this->category === self::CATEGORY_READING;
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
