<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreMangaRequest extends FormRequest
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
            'alternative_titles' => 'nullable|string',
            'type' => 'nullable|string',
            'status' => 'nullable|string',
            'year' => 'nullable|integer',
            'rating' => 'nullable|numeric',
            'score' => 'nullable|numeric',
            'source' => 'nullable|string',
            'author' => 'nullable|string',
            'artist' => 'nullable|string',
            'publisher' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:2048',
            'genres' => 'nullable|array',
            'genres.*' => 'integer|exists:manga_genres,id',
            'featured' => 'nullable|boolean',
        ];
    }
}
