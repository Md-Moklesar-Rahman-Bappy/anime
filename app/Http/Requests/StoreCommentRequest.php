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
            'episode_id' => ['required', 'integer', 'exists:episodes,id'],

            'body' => [
                'required',
                'string',
                'min:2',
                'max:1000',
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
     * ✅ Custom messages (better UX)
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
}