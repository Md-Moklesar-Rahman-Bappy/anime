<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Report extends Model
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_RESOLVED  = 'resolved';
    public const STATUS_DISMISSED = 'dismissed';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_RESOLVED,
        self::STATUS_DISMISSED,
    ];

    protected $fillable = [
        'episode_id',
        'user_id',
        'issue_type',
        'description',
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
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function ($report) {

            // ✅ default status
            if (empty($report->status)) {
                $report->status = self::STATUS_PENDING;
            }

            // ✅ normalize status
            $report->status = strtolower($report->status);
        });

        static::saved(fn() => static::clearCache());
        static::deleted(fn() => static::clearCache());
    }

    protected static function clearCache(): void
    {
        Cache::forget('admin_reports');
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
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    public function scopeDismissed($query)
    {
        return $query->where('status', self::STATUS_DISMISSED);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    public function isDismissed(): bool
    {
        return $this->status === self::STATUS_DISMISSED;
    }

    public function markResolved(): void
    {
        $this->update(['status' => self::STATUS_RESOLVED]);
    }

    public function markDismissed(): void
    {
        $this->update(['status' => self::STATUS_DISMISSED]);
    }
}
