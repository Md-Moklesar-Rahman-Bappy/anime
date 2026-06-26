<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MangaReadingHistory extends Model
{
    protected $table = 'manga_reading_history';

    protected $fillable = ['user_id', 'chapter_id', 'page_number', 'completed'];

    protected function casts(): array
    {
        return [
            'page_number' => 'integer',
            'completed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}
