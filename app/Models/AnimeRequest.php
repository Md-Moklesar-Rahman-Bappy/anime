<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimeRequest extends Model
{
    protected $table = 'requests';

    protected $fillable = [
        'user_id',
        'anime_title',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        // ✅ Default status
        static::creating(function ($model) {
            if (empty($model->status)) {
                $model->status = 'pending';
            }
        });

        // ✅ Optional: clear admin dashboard cache
        static::saved(fn () => cache()->forget('admin_requests'));
        static::deleted(fn () => cache()->forget('admin_requests'));
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
    | Scopes (VERY USEFUL)
    |--------------------------------------------------------------------------
    */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeDismissed($query)
    {
        return $query->where('status', 'dismissed');
    }
}