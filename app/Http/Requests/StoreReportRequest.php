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
            'episode_id' => 'required|exists:episodes,id',
            'issue_type' => 'required|string|in:broken,audio_not_synced,sub_not_synced,skip_time_wrong,other',
            'description' => 'nullable|string|max:2000',
        ];
    }
}
