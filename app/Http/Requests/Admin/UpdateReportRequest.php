<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ✅ Only admins / super admins can update reports
        return auth()->check() && in_array(
            strtolower(auth()->user()->role),
            ['admin', 'super_admin'],
            true
        );
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:pending,resolved,dismissed',
        ];
    }

    /**
     * ✅ Normalize data before validation
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => strtolower(trim($this->status)),
        ]);
    }

    /**
     * ✅ Custom error messages
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
        ];
    }
}