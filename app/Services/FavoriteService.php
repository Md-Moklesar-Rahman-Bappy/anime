<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class FavoriteService
{
    /*
    |--------------------------------------------------------------------------
    | TOGGLE FAVORITE
    |--------------------------------------------------------------------------
    */
    public function toggle(string $modelClass, string $foreignKey, int $userId, int $entityId): string
    {
        return DB::transaction(function () use ($modelClass, $foreignKey, $userId, $entityId) {

            $existing = $modelClass::where('user_id', $userId)
                ->where($foreignKey, $entityId)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $existing->delete();
                return 'removed';
            }

            $modelClass::create([
                'user_id' => $userId,
                $foreignKey => $entityId,
            ]);

            return 'added';
        });
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE LIST CATEGORY
    |--------------------------------------------------------------------------
    */
    public function updateList(
        string $modelClass,
        string $foreignKey,
        int $userId,
        int $entityId,
        ?string $category
    ): array {
        return DB::transaction(function () use ($modelClass, $foreignKey, $userId, $entityId, $category) {

            $category = $this->normalizeCategory($category);

            if (!$category) {
                $modelClass::where('user_id', $userId)
                    ->where($foreignKey, $entityId)
                    ->delete();

                return ['status' => 'ok', 'category' => null];
            }

            $fav = $modelClass::updateOrCreate(
                ['user_id' => $userId, $foreignKey => $entityId],
                ['category' => $category]
            );

            return [
                'status' => 'ok',
                'category' => $fav->category,
            ];
        });
    }

    /*
    |--------------------------------------------------------------------------
    | USER LIST
    |--------------------------------------------------------------------------
    */
    public function myList(
        string $modelClass,
        int $userId,
        ?string $activeCategory,
        array $categories,
        string $withRelation,
        int $perPage = 24
    ) {
        $query = $modelClass::query()
            ->where('user_id', $userId)
            ->with($withRelation);

        $activeCategory = $this->normalizeCategory($activeCategory);

        if ($activeCategory && in_array($activeCategory, $categories, true)) {
            $query->where('category', $activeCategory);
        } elseif ($activeCategory === 'favorites') {
            $query->whereNull('category');
        }

        return $query
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */
    protected function normalizeCategory(?string $category): ?string
    {
        $category = trim((string) $category);

        if ($category === '' || $category === 'null') {
            return null;
        }

        return strtolower($category);
    }
}
