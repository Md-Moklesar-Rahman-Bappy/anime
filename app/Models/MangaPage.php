<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MangaPage extends Model
{
    protected $fillable = ['chapter_id', 'page_number', 'image_path'];

    protected function casts(): array
    {
        return [
            'page_number' => 'integer',
        ];
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}
