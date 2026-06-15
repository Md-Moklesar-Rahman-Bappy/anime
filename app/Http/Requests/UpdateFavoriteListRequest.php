<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFavoriteListRequest extends FormRequest
{
    const CATEGORIES = ['watching', 'completed', 'plan_to_watch', 'on_hold', 'dropped'];

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'anime_id' => 'required|exists:anime,id',
            'category' => 'nullable|string|in:'.implode(',', self::CATEGORIES),
        ];
    }
}
