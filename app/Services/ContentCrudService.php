<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContentCrudService
{
    public function create(string $modelClass, array $data, ?array $genreIds = null, ?string $genreRelation = null): Model
    {
        return DB::transaction(function () use ($modelClass, $data, $genreIds, $genreRelation) {

            $data = $this->prepareData($data);
            $data = $this->handleFileUploads($modelClass, $data);

            $model = $modelClass::create($data);

            if ($genreIds !== null && $genreRelation) {
                $model->{$genreRelation}()->sync($genreIds);
            }

            return $model;
        });
    }

    public function update(Model $model, array $data, ?array $genreIds = null, ?string $genreRelation = null): Model
    {
        return DB::transaction(function () use ($model, $data, $genreIds, $genreRelation) {

            $data = $this->prepareData($data);
            $data = $this->handleFileUploads(get_class($model), $data, $model);

            $model->update($data);

            if ($genreIds !== null && $genreRelation) {
                $model->{$genreRelation}()->sync($genreIds);
            }

            return $model;
        });
    }

    public function delete(Model $model, array $fileFields = ['thumbnail', 'banner']): void
    {
        foreach ($fileFields as $field) {
            if ($model->{$field}) {
                Storage::disk('public')->delete($model->{$field});
            }
        }

        $model->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | Data Preparation
    |--------------------------------------------------------------------------
    */

    protected function prepareData(array $data): array
    {
        $title = $data['title'] ?? 'content';

        $data['slug'] = $data['slug'] ?? $this->generateUniqueSlug($title);

        $data['featured'] = $data['featured'] ?? false;

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | Slug Generator
    |--------------------------------------------------------------------------
    */

    protected function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        return $base . '-' . substr(md5(uniqid()), 0, 6);
    }

    /*
    |--------------------------------------------------------------------------
    | File Handling
    |--------------------------------------------------------------------------
    */

    protected function handleFileUploads(string $modelClass, array $data, ?Model $existing = null): array
    {
        $prefix = class_basename($modelClass);
        $folder = Str::lower(Str::plural($prefix));

        foreach (['thumbnail', 'banner'] as $field) {

            if (request()->hasFile($field)) {

                // ✅ delete old file
                if ($existing && $existing->{$field}) {
                    Storage::disk('public')->delete($existing->{$field});
                }

                $data[$field] = request()->file($field)
                    ->store("{$folder}/{$field}s", 'public');

            } elseif ($existing && !isset($data[$field])) {

                // ✅ preserve existing
                $data[$field] = $existing->{$field};
            }
        }

        return $data;
    }
}