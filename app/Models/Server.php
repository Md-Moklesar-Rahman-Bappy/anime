<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Server extends Model
{
    public const TYPES = [
        'embed',
        'm3u8',
        'mp4',
        'telegram',
        'youtube',
        'external',
    ];

    protected $fillable = [
        'episode_id',
        'label',
        'url',
        'type',
        'language',
        'priority', // ✅ important for ordering
    ];

    protected function casts(): array
    {
        return [
            'episode_id' => 'integer',
            'priority' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function ($server) {
            if (empty($server->priority)) {
                $server->priority = 0;
            }

            if ($server->language) {
                $server->language = strtolower($server->language);
            }
        });

        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }

    protected static function clearCache(): void
    {
        Cache::forget('episode_servers');
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

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForEpisode($query, int $episodeId)
    {
        return $query->where('episode_id', $episodeId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('priority');
    }

    public function scopeSub($query)
    {
        return $query->where('language', 'sub');
    }

    public function scopeDub($query)
    {
        return $query->where('language', 'dub');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (VERY IMPORTANT)
    |--------------------------------------------------------------------------
    */

    public function isYouTube(): bool
    {
        return $this->type === 'youtube';
    }

    public function isTelegram(): bool
    {
        return $this->type === 'telegram';
    }

    public function isM3U8(): bool
    {
        return $this->type === 'm3u8';
    }

    public function isMP4(): bool
    {
        return $this->type === 'mp4';
    }

    public function isEmbed(): bool
    {
        return $this->type === 'embed';
    }
}
