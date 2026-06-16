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
            'anime_id' => [
                'required',
                'integer',
                'exists:anime,id',
            ],
        ];
    }

    /**
     * ✅ Normalize input
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'anime_id' => (int) $this->anime_id,
        ]);
    }

    /**
     * ✅ Custom messages
     */
    public function messages(): array
    {
        return [
            'anime_id.required' => 'Anime is required.',
            'anime_id.integer' => 'Invalid anime ID.',
            'anime_id.exists' => 'Selected anime does not exist.',
        ];
    }
}