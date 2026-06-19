<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContentCrudService
{
    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create(
        string $modelClass,
        array $data,
        ?array $genreIds = null,
        ?string $genreRelation = null,
        array $files = []
    ): Model {
        return DB::transaction(function () use ($modelClass, $data, $genreIds, $genreRelation, $files) {

            $data = $this->prepareData($data);
            $data = $this->handleFileUploads($modelClass, $data, null, $files);

            $model = $modelClass::create($data);

            if ($genreIds !== null && $genreRelation) {
                $model->{$genreRelation}()->sync($genreIds);
            }

            return $model;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(
        Model $model,
        array $data,
        ?array $genreIds = null,
        ?string $genreRelation = null,
        array $files = []
    ): Model {
        return DB::transaction(function () use ($model, $data, $genreIds, $genreRelation, $files) {

            $data = $this->prepareData($data);
            $data = $this->handleFileUploads(get_class($model), $data, $model, $files);

            $model->update($data);

            if ($genreIds !== null && $genreRelation) {
                $model->{$genreRelation}()->sync($genreIds);
            }

            return $model;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function delete(Model $model, array $fileFields = ['thumbnail', 'banner']): void
    {
        DB::transaction(function () use ($model, $fileFields) {

            foreach ($fileFields as $field) {

                if ($model->{$field}) {
                    Storage::disk('public')->delete($model->{$field});
                }
            }

            $model->delete();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | PREPARE DATA
    |--------------------------------------------------------------------------
    */
    protected function prepareData(array $data): array
    {
        $title = trim((string) ($data['title'] ?? 'content'));

        if (!isset($data['slug']) || !$data['slug']) {
            $data['slug'] = $this->generateUniqueSlug($title);
        }

        $data['featured'] = (bool) ($data['featured'] ?? false);

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | SAFE SLUG GENERATOR
    |--------------------------------------------------------------------------
    */
    protected function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'content';
        $slug = $base;
        $counter = 1;

        while (app('db')->table('anime')->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /*
    |--------------------------------------------------------------------------
    | FILE HANDLING (DECOUPLED)
    |--------------------------------------------------------------------------
    */
    protected function handleFileUploads(
        string $modelClass,
        array $data,
        ?Model $existing = null,
        array $files = []
    ): array {
        $prefix = class_basename($modelClass);
        $folder = Str::lower(Str::plural($prefix));

        foreach (['thumbnail', 'banner'] as $field) {

            if (isset($files[$field])) {

                // ✅ delete old AFTER checking
                if ($existing && $existing->{$field}) {
                    Storage::disk('public')->delete($existing->{$field});
                }

                $data[$field] = $files[$field]->store(
                    "{$folder}/{$field}s",
                    'public'
                );
            } elseif ($existing && !array_key_exists($field, $data)) {

                // ✅ preserve existing
                $data[$field] = $existing->{$field};
            }
        }

        return $data;
    }
}
