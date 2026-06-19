<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MangaComment extends Model
{
    protected $fillable = [
        'chapter_id',
        'user_id',
        'body',
        'status',
    ];

    protected $casts = [
        'chapter_id' => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | CONSTANTS (IMPORTANT)
    |--------------------------------------------------------------------------
    */

    public const STATUS_VISIBLE = 'visible';
    public const STATUS_HIDDEN  = 'hidden';
    public const STATUS_PENDING = 'pending';

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function ($comment) {

            // ✅ default status
            if (empty($comment->status)) {
                $comment->status = self::STATUS_VISIBLE;
            }

            // ✅ normalize status
            $comment->status = strtolower($comment->status);
        });

        static::saved(fn() => static::clearCache());
        static::deleted(fn() => static::clearCache());
    }

    protected static function clearCache(): void
    {
        Cache::forget('chapter_comments');
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeByChapter($query, int $chapterId)
    {
        return $query->where('chapter_id', $chapterId);
    }

    public function scopeVisible($query)
    {
        return $query->where('status', self::STATUS_VISIBLE);
    }

    public function scopeLatestFirst($query)
    {
        return $query->latest();
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isVisible(): bool
    {
        return $this->status === self::STATUS_VISIBLE;
    }

    public function hide(): void
    {
        $this->update(['status' => self::STATUS_HIDDEN]);
    }

    public function approve(): void
    {
        $this->update(['status' => self::STATUS_VISIBLE]);
    }
}
