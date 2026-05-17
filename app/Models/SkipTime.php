<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkipTime extends Model
{
    protected $fillable = ['episode_id', 'intro_start', 'intro_end', 'outro_start', 'outro_end', 'user_id'];

    public function episode()
    {
        return $this->belongsTo(Episode::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
