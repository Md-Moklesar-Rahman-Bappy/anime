<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreEpisodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number' => 'required|integer',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'video_path' => 'nullable|string',
            'storage_disk' => 'nullable|string|in:local,s3,streaming',
            'source_type' => 'nullable|string|in:upload,youtube,telegram,external',
            'source_id' => 'nullable|string',
            'source_url' => 'nullable|string',
            'duration' => 'nullable|integer',
            'thumbnail' => 'nullable|image|max:2048',
            'has_sub' => 'nullable|boolean',
            'has_dub' => 'nullable|boolean',
            'air_date' => 'nullable|date',
            'language' => 'nullable|string',
            'youtube_url' => 'nullable|url',
            'uploaded_video_path' => 'nullable|string',
            'telegram_direct_url' => 'nullable|string',
            'telegram_file_id' => 'nullable|string',
            'telegram_duration' => 'nullable|integer',
            'telegram_thumb' => 'nullable|string',
            'source_label' => 'nullable|string|max:255',
        ];
    }
}
