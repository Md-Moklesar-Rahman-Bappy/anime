<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Episode extends Model
{
    protected $fillable = [
        'anime_id',
        'number',
        'title',
        'description',
        'video_path',
        'storage_disk',
        'source_type',
        'source_id',
        'source_url',
        'duration',
        'thumbnail',
        'has_sub',
        'has_dub',
        'air_date',
        'created_by',
        'telegram_message_id',
    ];

    protected $appends = ['thumbnail_url'];

    protected $casts = [
        'number' => 'integer',
        'duration' => 'integer',
        'has_sub' => 'boolean',
        'has_dub' => 'boolean',
        'air_date' => 'date',
        'telegram_message_id' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::saved(fn() => static::clearCache());
        static::deleted(fn() => static::clearCache());
    }

    protected static function clearCache(): void
    {
        Cache::forget('home_latest_episodes');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getThumbnailUrlAttribute(): string
    {
        if ($this->thumbnail) {
            return str_starts_with($this->thumbnail, 'http')
                ? $this->thumbnail
                : Storage::disk($this->storage_disk ?? 'public')->url($this->thumbnail);
        }

        return $this->anime?->thumbnail_url ?? '';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function anime()
    {
        return $this->belongsTo(Anime::class);
    }

    public function servers()
    {
        return $this->hasMany(Server::class)->orderBy('priority');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function skipTimes()
    {
        return $this->hasMany(SkipTime::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOrdered($query)
    {
        return $query->orderBy('number');
    }

    public function scopeLatest($query)
    {
        return $query->latest();
    }

    public function scopeForAnime($query, int $animeId)
    {
        return $query->where('anime_id', $animeId);
    }

    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    */

    public function previous()
    {
        return static::where('anime_id', $this->anime_id)
            ->where('number', '<', $this->number)
            ->orderByDesc('number')
            ->first();
    }

    public function next()
    {
        return static::where('anime_id', $this->anime_id)
            ->where('number', '>', $this->number)
            ->orderBy('number')
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Source Helpers (Player Logic)
    |--------------------------------------------------------------------------
    */

    public function isYouTube(): bool
    {
        return $this->source_type === 'youtube';
    }

    public function isTelegram(): bool
    {
        return $this->source_type === 'telegram';
    }

    public function isUpload(): bool
    {
        return $this->source_type === 'upload';
    }

    public function isExternal(): bool
    {
        return $this->source_type === 'external';
    }

    public function hasVideo(): bool
    {
        return filled($this->video_path)
            || filled($this->source_url)
            || filled($this->source_id);
    }

    /*
    |--------------------------------------------------------------------------
    | Player Helpers (NEW - VERY IMPORTANT)
    |--------------------------------------------------------------------------
    */

    public function bestServer()
    {
        return $this->servers()->first();
    }

    public function hasServers(): bool
    {
        return $this->servers()->exists();
    }
}
