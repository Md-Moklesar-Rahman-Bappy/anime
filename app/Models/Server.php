<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    protected $fillable = ['episode_id', 'label', 'url', 'type', 'language'];

    public function episode()
    {
        return $this->belongsTo(Episode::class);
    }
}
