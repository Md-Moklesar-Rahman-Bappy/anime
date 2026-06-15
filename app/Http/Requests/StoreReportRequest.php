<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'episode_id' => ['required', 'integer', 'exists:episodes,id'],

            'issue_type' => [
                'required',
                'string',
                'in:broken,audio_not_synced,sub_not_synced,skip_time_wrong,other',
            ],

            'description' => [
                'nullable',
                'string',
                'min:5',
                'max:2000',
                'not_regex:/^(.)\1+$/', // ✅ anti-spam (e.g. "aaaaa")
            ],
        ];
    }

    /**
     * ✅ Normalize input
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'issue_type' => strtolower(trim($this->issue_type)),
            'description' => $this->description ? trim($this->description) : null,
        ]);
    }

    /**
     * ✅ Custom messages
     */
    public function messages(): array
    {
        return [
            'episode_id.required' => 'Episode is required.',
            'episode_id.exists' => 'Invalid episode selected.',

            'issue_type.required' => 'Please select an issue type.',
            'issue_type.in' => 'Invalid issue type.',

            'description.min' => 'Description must be at least 5 characters.',
            'description.max' => 'Description is too long (max 2000 characters).',
            'description.not_regex' => 'Invalid or spam-like description.',
        ];
    }
}
