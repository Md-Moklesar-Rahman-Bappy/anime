<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()?->id),
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
            'name' => $this->clean($this->input('name')),
            'email' => strtolower($this->clean($this->input('email'))),
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
            'name.required' => 'Name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already in use.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */
    protected function clean(?string $value): string
    {
        return trim((string) $value);
    }
}