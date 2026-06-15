<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreEpisodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role !== 'user';
    }

    public function rules(): array
    {
        return [
            'number' => 'required|integer|min:1',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:3000',

            'video_path' => 'nullable|string|max:500',

            'storage_disk' => 'nullable|string|in:local,s3,streaming',

            'source_type' => 'required|string|in:upload,youtube,telegram,external',

            'source_id' => 'nullable|string|max:255',
            'source_url' => 'nullable|url|max:1000',

            'youtube_url' => 'nullable|url',
            'uploaded_video_path' => 'nullable|string|max:500',

            'telegram_direct_url' => 'nullable|url',
            'telegram_file_id' => 'nullable|string|max:255',
            'telegram_duration' => 'nullable|integer|min:0|max:100000',
            'telegram_thumb' => 'nullable|string|max:500',

            'duration' => 'nullable|integer|min:0|max:20000',

            'thumbnail' => 'nullable|image|mimes:jpeg,png,webp|max:2048',

            'has_sub' => 'nullable|boolean',
            'has_dub' => 'nullable|boolean',

            'air_date' => 'nullable|date|before_or_equal:today',

            'language' => 'nullable|string|max:50',
            'source_label' => 'nullable|string|max:255',

            // ✅ Conditional rules
            'youtube_url' => 'required_if:source_type,youtube',
            'telegram_file_id' => 'required_if:source_type,telegram',
            'uploaded_video_path' => 'required_if:source_type,upload',
            'source_url' => 'required_if:source_type,external',
        ];
    }

    /**
     * ✅ Clean & normalize input
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => $this->title ? trim($this->title) : null,
            'description' => $this->description ? trim($this->description) : null,
            'source_type' => strtolower($this->source_type),
            'language' => $this->language ? strtolower($this->language) : null,
        ]);
    }

    /**
     * ✅ Custom validation messages (optional but useful)
     */
    public function messages(): array
    {
        return [
            'youtube_url.required_if' => 'YouTube URL is required when using YouTube source.',
            'telegram_file_id.required_if' => 'Telegram file ID is required for Telegram source.',
            'uploaded_video_path.required_if' => 'Upload path is required for uploaded videos.',
            'source_url.required_if' => 'External URL is required for external source.',
        ];
    }
}