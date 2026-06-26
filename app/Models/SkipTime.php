<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkipTime extends Model
{
    protected $fillable = ['episode_id', 'intro_start', 'intro_end', 'outro_start', 'outro_end', 'user_id'];

    protected function casts(): array
    {
        return [
            'intro_start' => 'integer',
            'intro_end' => 'integer',
            'outro_start' => 'integer',
            'outro_end' => 'integer',
        ];
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
