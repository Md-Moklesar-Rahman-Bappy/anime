<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_USER        = 'user';
    public const ROLE_ADMIN       = 'admin';
    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLES = [
        self::ROLE_USER,
        self::ROLE_ADMIN,
        self::ROLE_SUPER_ADMIN,
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

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot logic
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::saving(function ($user) {

            // ✅ default role
            if (empty($user->role)) {
                $user->role = self::ROLE_USER;
            }

            // ✅ normalize username
            if ($user->username) {
                $user->username = strtolower(trim($user->username));
            }
        });

        static::saved(fn($user) => Cache::forget('user_' . $user->id));
    }

    /*
    |--------------------------------------------------------------------------
    | Role Helpers
    |--------------------------------------------------------------------------
    */

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN], true);
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    public function canModerate(): bool
    {
        return $this->isAdmin();
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships (Anime)
    |--------------------------------------------------------------------------
    */

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function watchHistory()
    {
        return $this->hasMany(WatchHistory::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteAnime()
    {
        return $this->belongsToMany(Anime::class, 'favorites');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function requests()
    {
        return $this->hasMany(AnimeRequest::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Manga Relationships
    |--------------------------------------------------------------------------
    */

    public function mangaFavorites()
    {
        return $this->hasMany(MangaFavorite::class);
    }

    public function favoriteManga()
    {
        return $this->belongsToMany(Manga::class, 'manga_favorites');
    }

    public function mangaReadingHistory()
    {
        return $this->hasMany(MangaReadingHistory::class);
    }

    public function mangaComments()
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
        return $query->whereIn('role', [
            self::ROLE_ADMIN,
            self::ROLE_SUPER_ADMIN
        ]);
    }

    public function scopeUsers($query)
    {
        return $query->where('role', self::ROLE_USER);
    }
}
