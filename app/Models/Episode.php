<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Episode extends Model
{
    protected $fillable = [
        'anime_id', 'number', 'title', 'description', 'video_path',
        'storage_disk', 'source_type', 'source_id', 'source_url',
        'duration', 'thumbnail', 'has_sub', 'has_dub',
        'air_date', 'created_by', 'telegram_message_id',
    ];

    protected $appends = ['thumbnail_url'];

    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'duration' => 'integer',
            'has_sub' => 'boolean',
            'has_dub' => 'boolean',
            'air_date' => 'date',
            'telegram_message_id' => 'integer',
        ];
    }

    public function getThumbnailUrlAttribute(): string
    {
        if ($this->thumbnail) {
            return str_starts_with($this->thumbnail, 'http') ? $this->thumbnail : Storage::url($this->thumbnail);
        }

        return $this->anime?->thumbnail_url ?? '';
    }

    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }

    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function skipTimes(): HasMany
    {
        return $this->hasMany(SkipTime::class);
    }
}
