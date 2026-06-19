<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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

    protected $casts = [
        'total_size' => 'integer',
        'chunk_size' => 'integer',
        'total_chunks' => 'integer',
        'received_chunks' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | CONSTANTS (IMPORTANT)
    |--------------------------------------------------------------------------
    */

    public const STATUS_PENDING   = 'pending';
    public const STATUS_UPLOADING = 'uploading';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED    = 'failed';

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function ($upload) {

            // ✅ default status
            if (empty($upload->status)) {
                $upload->status = self::STATUS_PENDING;
            }

            // ✅ normalize status
            $upload->status = strtolower($upload->status);

            // ✅ prevent overflow
            if ($upload->received_chunks > $upload->total_chunks) {
                $upload->received_chunks = $upload->total_chunks;
            }
        });

        static::saved(fn() => static::clearCache());
        static::deleted(fn() => static::clearCache());
    }

    protected static function clearCache(): void
    {
        Cache::forget('active_uploads');
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

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeUploading($query)
    {
        return $query->where('status', self::STATUS_UPLOADING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
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
            'status' => self::STATUS_COMPLETED,
            'final_path' => $finalPath,
        ]);
    }

    public function markUploading(): void
    {
        $this->update([
            'status' => self::STATUS_UPLOADING,
        ]);
    }

    public function markFailed(): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
        ]);
    }

    public function incrementChunk(): void
    {
        if ($this->received_chunks < $this->total_chunks) {
            $this->increment('received_chunks');
        }
    }
}
