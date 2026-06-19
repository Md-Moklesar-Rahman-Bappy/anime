<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class WatchHistory extends Model
{
    protected $fillable = [
        'user_id',
        'episode_id',
        'progress',
        'completed',
    ];

    protected $casts = [
        'progress' => 'integer',
        'completed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function ($model) {

            // ✅ prevent negative progress
            if (empty($model->progress) || $model->progress < 0) {
                $model->progress = 0;
            }

            // ✅ default completion
            if ($model->completed === null) {
                $model->completed = false;
            }
        });

        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }

    protected static function clearCache(): void
    {
        Cache::forget('user_watch_history');
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

    public function episode()
    {
        return $this->belongsTo(Episode::class);
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
        $duration = $this->episode?->duration ?? 0;

        if ($duration === 0) {
            return 0;
        }

        return round(($this->progress / $duration) * 100, 2);
    }

    public function isCompleted(): bool
    {
        return $this->completed === true;
    }

    public function markCompleted(): void
    {
        $this->update([
            'completed' => true,
        ]);
    }

    public static function updateProgress(int $userId, int $episodeId, int $progress): self
    {
        return static::updateOrCreate(
            [
                'user_id' => $userId,
                'episode_id' => $episodeId,
            ],
            [
                'progress' => max(0, $progress),
                'completed' => false,
            ]
        );
    }
}