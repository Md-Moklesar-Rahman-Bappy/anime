<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Comment extends Model
{
    protected $fillable = [
        'episode_id',
        'user_id',
        'body',
        'status',
    ];

    protected $casts = [
        'episode_id' => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | CONSTANTS (VERY IMPORTANT)
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

            // ✅ Default status
            if (empty($comment->status)) {
                $comment->status = self::STATUS_VISIBLE;
            }

            // ✅ Normalize status
            $comment->status = strtolower($comment->status);
        });

        static::saved(fn() => static::clearCache());
        static::deleted(fn() => static::clearCache());
    }

    protected static function clearCache(): void
    {
        Cache::forget('episode_comments');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function episode()
    {
        return $this->belongsTo(Episode::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (VERY IMPORTANT)
    |--------------------------------------------------------------------------
    */

    public function scopeByEpisode($query, int $episodeId)
    {
        return $query->where('episode_id', $episodeId);
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
