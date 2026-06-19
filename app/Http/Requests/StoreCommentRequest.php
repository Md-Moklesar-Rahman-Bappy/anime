<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
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
            'episode_id' => ['required', 'integer', 'exists:episodes,id'],

            'body' => [
                'required',
                'string',
                'min:2',
                'max:1000',
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
            'body' => $this->cleanNullable($this->input('body')),
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
            'episode_id.required' => 'Episode is required.',
            'episode_id.exists' => 'Invalid episode selected.',

            'body.required' => 'Comment cannot be empty.',
            'body.min' => 'Comment must be at least 2 characters.',
            'body.max' => 'Comment is too long (max 1000 characters).',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */
    protected function cleanNullable(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}