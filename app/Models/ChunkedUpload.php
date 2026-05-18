<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChunkedUpload extends Model
{
    protected $fillable = [
        'user_id', 'filename', 'mime_type', 'total_size', 'chunk_size',
        'total_chunks', 'received_chunks', 'temp_dir', 'status', 'final_path',
    ];

    protected function casts(): array
    {
        return [
            'total_size' => 'integer',
            'chunk_size' => 'integer',
            'total_chunks' => 'integer',
            'received_chunks' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
