<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnimeRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],

            'type' => ['nullable', 'string', 'in:TV,Movie,OVA,ONA,Special'],
            'status' => ['nullable', 'string', 'in:Ongoing,Completed,Not Yet Aired'],

            'country' => ['nullable', 'string', 'max:100'],
            'season' => ['nullable', 'string', 'in:Winter,Spring,Summer,Fall'],

            'year' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:10'],

            'age_rating' => ['nullable', 'string', 'max:50'],

            'episodes_count' => ['nullable', 'integer', 'min:0', 'max:5000'],
            'duration' => ['nullable', 'integer', 'min:0', 'max:1000'],

            'source' => ['nullable', 'string', 'max:255'],
            'studio' => ['nullable', 'string', 'max:255'],
            'producers' => ['nullable', 'string', 'max:500'],
            'licensors' => ['nullable', 'string', 'max:500'],

            /*
            |--------------------------------------------------------------------------
            | IMAGES
            |--------------------------------------------------------------------------
            */
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'banner' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:4096'],

            /*
            |--------------------------------------------------------------------------
            | GENRES
            |--------------------------------------------------------------------------
            */
            'genres' => ['nullable', 'array'],
            'genres.*' => ['integer', 'exists:genres,id'],

            /*
            |--------------------------------------------------------------------------
            | FLAGS
            |--------------------------------------------------------------------------
            */
            'featured' => ['nullable', 'boolean'],
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
            'title' => $this->clean($this->input('title')),
            'description' => $this->cleanNullable($this->input('description')),

            'type' => $this->normalizeEnum($this->input('type')),
            'status' => $this->normalizeEnum($this->input('status')),
            'season' => $this->normalizeEnum($this->input('season')),

            'country' => $this->cleanNullable($this->input('country')),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    protected function clean(?string $value): ?string
    {
        return trim((string) $value);
    }

    protected function cleanNullable(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value !== '' ? $value : null;
    }

    protected function normalizeEnum(?string $value): ?string
    {
        if (!$value) return null;

        $value = strtolower(trim($value));

        return match ($value) {
            'tv' => 'TV',
            'movie' => 'Movie',
            'ova' => 'OVA',
            'ona' => 'ONA',
            'special' => 'Special',

            'ongoing' => 'Ongoing',
            'completed' => 'Completed',
            'not yet aired' => 'Not Yet Aired',

            'winter' => 'Winter',
            'spring' => 'Spring',
            'summer' => 'Summer',
            'fall' => 'Fall',

            default => ucfirst($value),
        };
    }
}
