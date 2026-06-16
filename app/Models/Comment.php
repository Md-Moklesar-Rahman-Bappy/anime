<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Comment extends Model
{
    protected $fillable = [
        'episode_id',
        'user_id',
        'body',
        'status', // ✅ moderation support
    ];

    protected function casts(): array
    {
        return [
            'episode_id' => 'integer',
            'user_id' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function ($comment) {
            if (empty($comment->status)) {
                $comment->status = 'visible';
            }
        });

        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
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

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    public function user(): BelongsTo
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
        return $query->where('status', 'visible');
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderByDesc('created_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isVisible(): bool
    {
        return $this->status === 'visible';
    }
}
