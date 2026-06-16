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
            'chapter_id' => ['required', 'integer', 'exists:chapters,id'],

            'body' => [
                'required',
                'string',
                'min:2',
                'max:5000',
                'not_regex:/^(.)\1+$/', // ✅ prevent spam like "aaaaaaa"
            ],
        ];
    }

    /**
     * ✅ Normalize input before validation
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'body' => $this->body ? trim($this->body) : null,
        ]);
    }

    /**
     * ✅ Custom error messages (better UX)
     */
    public function messages(): array
    {
        return [
            'chapter_id.required' => 'Chapter is required.',
            'chapter_id.exists' => 'Invalid chapter selected.',

            'body.required' => 'Comment cannot be empty.',
            'body.min' => 'Comment must be at least 2 characters.',
            'body.max' => 'Comment is too long (max 5000 characters).',
            'body.not_regex' => 'Spam-like comment detected.',
        ];
    }
}