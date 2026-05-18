<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function watchHistory(): HasMany
    {
        return $this->hasMany(WatchHistory::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteAnime(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class, 'favorites');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(AnimeRequest::class);
    }
}
