<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLES = [
        'user',
        'admin',
        'super_admin',
    ];

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

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::saving(function ($user) {
            if ($user->role === null) {
                $user->role = 'user';
            }

            if ($user->username) {
                $user->username = strtolower(trim($user->username));
            }
        });

        static::saved(fn () => Cache::forget('user_'.$this->id ?? ''));
    }

    /*
    |--------------------------------------------------------------------------
    | Role Helpers
    |--------------------------------------------------------------------------
    */

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin'], true);
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function canModerate(): bool
    {
        return $this->isAdmin();
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Manga Relationships (IMPORTANT)
    |--------------------------------------------------------------------------
    */

    public function mangaFavorites(): HasMany
    {
        return $this->hasMany(MangaFavorite::class);
    }

    public function favoriteManga(): BelongsToMany
    {
        return $this->belongsToMany(Manga::class, 'manga_favorites');
    }

    public function mangaReadingHistory(): HasMany
    {
        return $this->hasMany(MangaReadingHistory::class);
    }

    public function mangaComments(): HasMany
    {
        return $this->hasMany(MangaComment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeAdmins($query)
    {
        return $query->whereIn('role', ['admin', 'super_admin']);
    }

    public function scopeUsers($query)
    {
        return $query->where('role', 'user');
    }
}