<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreMangaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role !== 'user';
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'alternative_titles' => 'nullable|string|max:500',

            'type' => 'nullable|string|in:Manga,Manhwa,Manhua,One-shot,Doujinshi',
            'status' => 'nullable|string|in:Ongoing,Completed,Hiatus,Canceled',

            'year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'rating' => 'nullable|numeric|min:0|max:10',
            'score' => 'nullable|numeric|min:0|max:10',

            'source' => 'nullable|string|max:255',

            'author' => 'nullable|string|max:255',
            'artist' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',

            // ✅ Media safety
            'thumbnail' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,webp|max:4096',

            // ✅ Genres
            'genres' => 'nullable|array',
            'genres.*' => 'integer|exists:manga_genres,id',

            // ✅ Feature flag
            'featured' => 'nullable|boolean',
        ];
    }

    /**
     * ✅ Normalize data before validation
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim($this->title),
            'description' => $this->description ? trim($this->description) : null,
            'alternative_titles' => $this->alternative_titles ? trim($this->alternative_titles) : null,

            'type' => $this->type ? ucfirst(strtolower($this->type)) : null,
            'status' => $this->status ? ucfirst(strtolower($this->status)) : null,

            'author' => $this->author ? trim($this->author) : null,
            'artist' => $this->artist ? trim($this->artist) : null,
            'publisher' => $this->publisher ? trim($this->publisher) : null,
        ]);
    }

    /**
     * ✅ Custom error messages (better UX)
     */
    public function messages(): array
    {
        return [
            'type.in' => 'Invalid manga type selected.',
            'status.in' => 'Invalid status value.',
            'year.max' => 'Year cannot be in the future.',
            'rating.max' => 'Rating must be between 0 and 10.',
            'score.max' => 'Score must be between 0 and 10.',
        ];
    }
}