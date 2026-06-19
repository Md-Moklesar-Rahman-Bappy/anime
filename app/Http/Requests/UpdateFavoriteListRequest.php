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

    /*
    |--------------------------------------------------------------------------
    | AUTHORIZE
    |--------------------------------------------------------------------------
    */
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /*
    |--------------------------------------------------------------------------
    | RULES
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | NORMALIZE INPUT
    |--------------------------------------------------------------------------
    */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'anime_id' => (int) $this->input('anime_id'),

            'category' => $this->cleanNullableLower(
                $this->input('category')
            ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CUSTOM MESSAGES
    |--------------------------------------------------------------------------
    */
    public function messages(): array
    {
        return [
            'anime_id.required' => 'Anime is required.',
            'anime_id.exists' => 'Selected anime does not exist.',

            'category.in' => 'Invalid category selected.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */
    protected function cleanNullableLower(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? strtolower($value) : null;
    }
}