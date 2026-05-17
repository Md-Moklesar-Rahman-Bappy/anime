<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChunkedUpload extends Model
{
    protected $fillable = [
        'user_id', 'filename', 'mime_type', 'total_size', 'chunk_size',
        'total_chunks', 'received_chunks', 'temp_dir', 'status', 'final_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
