<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ContentCrudService
{
    public function create(string $modelClass, array $data, ?array $genreIds = null, ?string $genreRelation = null): Model
    {
        $data['slug'] = Str::slug($data['title']);
        $data['featured'] = request()->has('featured');

        $data = $this->handleFileUploads($modelClass, $data);

        $model = $modelClass::create($data);

        if ($genreIds !== null && $genreRelation) {
            $model->{$genreRelation}()->sync($genreIds);
        }

        return $model;
    }

    public function update(Model $model, array $data, ?array $genreIds = null, ?string $genreRelation = null): Model
    {
        $data['slug'] = Str::slug($data['title']);
        $data['featured'] = request()->has('featured');

        $data = $this->handleFileUploads(get_class($model), $data, $model);

        $model->update($data);

        if ($genreIds !== null && $genreRelation) {
            $model->{$genreRelation}()->sync($genreIds);
        }

        return $model;
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

    protected function handleFileUploads(string $modelClass, array $data, ?Model $existing = null): array
    {
        $prefix = class_basename($modelClass);
        $lowerPrefix = Str::lower(Str::plural($prefix));

        if ($file = request()->file('thumbnail')) {
            $data['thumbnail'] = $file->store("{$lowerPrefix}/thumbnails", 'public');
        } elseif ($existing && !isset($data['thumbnail'])) {
            $data['thumbnail'] = $existing->thumbnail;
        }

        if ($file = request()->file('banner')) {
            $data['banner'] = $file->store("{$lowerPrefix}/banners", 'public');
        } elseif ($existing && !isset($data['banner'])) {
            $data['banner'] = $existing->banner;
        }

        return $data;
    }
}
