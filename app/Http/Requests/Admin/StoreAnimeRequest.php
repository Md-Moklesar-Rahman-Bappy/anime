<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string',
            'status' => 'nullable|string',
            'country' => 'nullable|string',
            'season' => 'nullable|string',
            'year' => 'nullable|integer',
            'rating' => 'nullable|numeric',
            'score' => 'nullable|numeric',
            'age_rating' => 'nullable|string',
            'episodes_count' => 'nullable|integer',
            'duration' => 'nullable|integer',
            'source' => 'nullable|string',
            'studio' => 'nullable|string',
            'producers' => 'nullable|string',
            'licensors' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:2048',
            'genres' => 'nullable|array',
            'genres.*' => 'integer|exists:genres,id',
            'featured' => 'nullable|boolean',
        ];
    }
}
