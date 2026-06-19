<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Server extends Model
{
    public const TYPE_EMBED    = 'embed';
    public const TYPE_M3U8     = 'm3u8';
    public const TYPE_MP4      = 'mp4';
    public const TYPE_TELEGRAM = 'telegram';
    public const TYPE_YOUTUBE  = 'youtube';
    public const TYPE_EXTERNAL = 'external';

    public const TYPES = [
        self::TYPE_EMBED,
        self::TYPE_M3U8,
        self::TYPE_MP4,
        self::TYPE_TELEGRAM,
        self::TYPE_YOUTUBE,
        self::TYPE_EXTERNAL,
    ];

    protected $fillable = [
        'episode_id',
        'label',
        'url',
        'type',
        'language',
        'priority',
    ];

    protected $casts = [
        'episode_id' => 'integer',
        'priority' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function ($server) {

            // ✅ default priority
            if (is_null($server->priority)) {
                $server->priority = 0;
            }

            // ✅ normalize values
            $server->language = $server->language
                ? strtolower($server->language)
                : null;

            $server->type = $server->type
                ? strtolower($server->type)
                : null;
        });

        static::saved(fn() => static::clearCache());
        static::deleted(fn() => static::clearCache());
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

    public function episode()
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
        return $query->orderBy('priority');
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
    | Helpers (PLAYER LOGIC)
    |--------------------------------------------------------------------------
    */

    public function isYouTube(): bool
    {
        return $this->type === self::TYPE_YOUTUBE;
    }

    public function isTelegram(): bool
    {
        return $this->type === self::TYPE_TELEGRAM;
    }

    public function isM3U8(): bool
    {
        return $this->type === self::TYPE_M3U8;
    }

    public function isMP4(): bool
    {
        return $this->type === self::TYPE_MP4;
    }

    public function isEmbed(): bool
    {
        return $this->type === self::TYPE_EMBED;
    }

    public function isExternal(): bool
    {
        return $this->type === self::TYPE_EXTERNAL;
    }
}
