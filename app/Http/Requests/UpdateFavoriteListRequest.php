<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFavoriteListRequest extends FormRequest
{
    public const CATEGORIES = [
        'watching',
        'completed',
        'plan_to_watch',
        'on_hold',
        'dropped',
    ];

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

            'category' => [
                'nullable',
                'string',
                'in:' . implode(',', self::CATEGORIES),
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
            'category' => $this->category
                ? strtolower(trim($this->category))
                : null,
        ]);
    }

    /**
     * ✅ Custom messages
     */
    public function messages(): array
    {
        return [
            'anime_id.required' => 'Anime is required.',
            'anime_id.exists' => 'Selected anime does not exist.',

            'category.in' => 'Invalid category selected.',
        ];
    }
}
