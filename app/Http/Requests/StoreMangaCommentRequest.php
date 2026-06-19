<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMangaCommentRequest extends FormRequest
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
            'chapter_id' => ['required', 'integer', 'exists:chapters,id'],

            'body' => [
                'required',
                'string',
                'min:2',
                'max:5000',

                // ✅ Prevent spam like "aaaaaaa"
                'not_regex:/^(.)\1+$/',

                // ✅ Prevent whitespace-only spam
                'not_regex:/^\s+$/',
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
            'chapter_id.required' => 'Chapter is required.',
            'chapter_id.exists' => 'Invalid chapter selected.',

            'body.required' => 'Comment cannot be empty.',
            'body.min' => 'Comment must be at least 2 characters.',
            'body.max' => 'Comment is too long (max 5000 characters).',
            'body.not_regex' => 'Spam-like or invalid comment detected.',
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