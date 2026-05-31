<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMangaCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'chapter_id' => 'required|exists:chapters,id',
            'body' => 'required|string|max:5000',
        ];
    }
}
