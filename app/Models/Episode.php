<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    protected $fillable = [
        'anime_id', 'number', 'title', 'description', 'video_path',
        'storage_disk', 'duration', 'thumbnail', 'has_sub', 'has_dub',
        'air_date', 'created_by',
    ];

    public function anime()
    {
        return $this->belongsTo(Anime::class);
    }

    public function servers()
    {
        return $this->hasMany(Server::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function skipTimes()
    {
        return $this->hasMany(SkipTime::class);
    }
}
