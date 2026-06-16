<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ✅ You can replace with role check if needed
        return auth()->check() && auth()->user()->role !== 'user';
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',

            'type' => 'nullable|string|in:TV,Movie,OVA,ONA,Special',
            'status' => 'nullable|string|in:Ongoing,Completed,Not Yet Aired',

            'country' => 'nullable|string|max:100',
            'season' => 'nullable|string|in:Winter,Spring,Summer,Fall',

            'year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'rating' => 'nullable|numeric|min:0|max:10',
            'score' => 'nullable|numeric|min:0|max:10',

            'age_rating' => 'nullable|string|max:50',

            'episodes_count' => 'nullable|integer|min:0|max:5000',
            'duration' => 'nullable|integer|min:0|max:1000',

            'source' => 'nullable|string|max:255',
            'studio' => 'nullable|string|max:255',
            'producers' => 'nullable|string|max:500',
            'licensors' => 'nullable|string|max:500',

            // ✅ Images (secure)
            'thumbnail' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,webp|max:4096',

            // ✅ Genres
            'genres' => 'nullable|array',
            'genres.*' => 'integer|exists:genres,id',

            // ✅ Featured
            'featured' => 'nullable|boolean',
        ];
    }

    /**
     * ✅ Normalize/clean input before validation
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim($this->title),
            'description' => $this->description ? trim($this->description) : null,
            'type' => $this->type ? ucfirst(strtolower($this->type)) : null,
            'status' => $this->status ? ucfirst(strtolower($this->status)) : null,
            'season' => $this->season ? ucfirst(strtolower($this->season)) : null,
        ]);
    }
}