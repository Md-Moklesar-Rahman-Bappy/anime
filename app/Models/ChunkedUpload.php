<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class ChunkedUpload extends Model
{
    protected $fillable = [
        'user_id',
        'filename',
        'mime_type',
        'total_size',
        'chunk_size',
        'total_chunks',
        'received_chunks',
        'temp_dir',
        'status',
        'final_path',
    ];

    protected function casts(): array
    {
        return [
            'total_size' => 'integer',
            'chunk_size' => 'integer',
            'total_chunks' => 'integer',
            'received_chunks' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function ($upload) {
            if (empty($upload->status)) {
                $upload->status = 'pending';
            }

            if ($upload->received_chunks > $upload->total_chunks) {
                $upload->received_chunks = $upload->total_chunks;
            }
        });

        static::saved(fn () => Cache::forget('active_uploads'));
        static::deleted(fn () => Cache::forget('active_uploads'));
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeUploading($query)
    {
        return $query->where('status', 'uploading');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (VERY IMPORTANT)
    |--------------------------------------------------------------------------
    */

    public function progress(): float
    {
        if ($this->total_chunks === 0) {
            return 0;
        }

        return round(($this->received_chunks / $this->total_chunks) * 100, 2);
    }

    public function isComplete(): bool
    {
        return $this->received_chunks >= $this->total_chunks;
    }

    public function markCompleted(string $finalPath): void
    {
        $this->update([
            'status' => 'completed',
            'final_path' => $finalPath,
        ]);
    }

    public function incrementChunk(): void
    {
        if ($this->received_chunks < $this->total_chunks) {
            $this->increment('received_chunks');
        }
    }
}