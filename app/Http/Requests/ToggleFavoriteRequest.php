<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ToggleFavoriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'anime_id' => 'required|exists:anime,id',
        ];
    }
}
