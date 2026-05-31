<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'episode_id' => 'required|exists:episodes,id',
            'body' => 'required|string|max:1000',
        ];
    }
}
