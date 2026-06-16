<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class SkipTime extends Model
{
    protected $fillable = [
        'episode_id',
        'intro_start',
        'intro_end',
        'outro_start',
        'outro_end',
        'user_id'
    ];

    protected function casts(): array
    {
        return [
            'intro_start' => 'integer',
            'intro_end' => 'integer',
            'outro_start' => 'integer',
            'outro_end' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::saving(function ($model) {
            // ✅ Validate intro
            if ($model->intro_start >= $model->intro_end) {
                $model->intro_start = 0;
                $model->intro_end = 0;
            }

            // ✅ Validate outro
            if ($model->outro_start >= $model->outro_end) {
                $model->outro_start = 0;
                $model->outro_end = 0;
            }
        });

        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }

    protected static function clearCache(): void
    {
        Cache::forget('episode_skip_times');
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
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForEpisode($query, int $episodeId)
    {
        return $query->where('episode_id', $episodeId);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (VERY IMPORTANT)
    |--------------------------------------------------------------------------
    */

    public function hasIntro(): bool
    {
        return $this->intro_start < $this->intro_end;
    }

    public function hasOutro(): bool
    {
        return $this->outro_start < $this->outro_end;
    }

    public function isInIntro(int $currentTime): bool
    {
        return $this->hasIntro()
            && $currentTime >= $this->intro_start
            && $currentTime <= $this->intro_end;
    }

    public function isInOutro(int $currentTime): bool
    {
        return $this->hasOutro()
            && $currentTime >= $this->outro_start
            && $currentTime <= $this->outro_end;
    }

    public function getIntroDuration(): int
    {
        return $this->intro_end - $this->intro_start;
    }

    public function getOutroDuration(): int
    {
        return $this->outro_end - $this->outro_start;
    }
}