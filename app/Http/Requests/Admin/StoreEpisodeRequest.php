<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreEpisodeRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | AUTHORIZE
    |--------------------------------------------------------------------------
    */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && in_array($user->role, ['admin', 'super_admin'], true);
    }

    /*
    |--------------------------------------------------------------------------
    | RULES
    |--------------------------------------------------------------------------
    */
    public function rules(): array
    {
        return [
            'number'       => ['required', 'integer', 'min:1'],
            'title'        => ['nullable', 'string', 'max:255'],
            'description'  => ['nullable', 'string', 'max:3000'],

            'video_path'   => ['nullable', 'string', 'max:500'],
            'storage_disk' => ['nullable', 'string', 'in:local,s3,streaming'],

            'source_type'  => ['required', 'string', 'in:upload,youtube,telegram,external'],

            'source_id'    => ['nullable', 'string', 'max:255'],
            'source_url'   => ['nullable', 'url', 'max:1000'],

            /*
            |--------------------------------------------------------------------------
            | SOURCE-SPECIFIC INPUTS
            |--------------------------------------------------------------------------
            */
            'youtube_url'          => ['nullable', 'url', 'required_if:source_type,youtube'],
            'uploaded_video_path'  => ['nullable', 'string', 'max:500', 'required_if:source_type,upload'],

            'telegram_direct_url'  => ['nullable', 'url'],
            'telegram_file_id'     => ['nullable', 'string', 'max:255', 'required_if:source_type,telegram'],
            'telegram_duration'    => ['nullable', 'integer', 'min:0', 'max:100000'],
            'telegram_thumb'       => ['nullable', 'string', 'max:500'],

            'duration' => ['nullable', 'integer', 'min:0', 'max:20000'],

            /*
            |--------------------------------------------------------------------------
            | MEDIA
            |--------------------------------------------------------------------------
            */
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],

            /*
            |--------------------------------------------------------------------------
            | FLAGS
            |--------------------------------------------------------------------------
            */
            'has_sub' => ['nullable', 'boolean'],
            'has_dub' => ['nullable', 'boolean'],

            /*
            |--------------------------------------------------------------------------
            | META
            |--------------------------------------------------------------------------
            */
            'air_date'     => ['nullable', 'date', 'before_or_equal:today'],
            'language'     => ['nullable', 'string', 'max:50'],
            'source_label' => ['nullable', 'string', 'max:255'],
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
            'title' => $this->cleanNullable($this->input('title')),
            'description' => $this->cleanNullable($this->input('description')),

            'source_type' => strtolower((string) $this->input('source_type')),
            'language'    => $this->cleanNullable($this->input('language')),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | MESSAGES
    |--------------------------------------------------------------------------
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
