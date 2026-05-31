<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;

class FavoriteService
{
    public function toggle(string $modelClass, string $foreignKey, int $userId, int $entityId): JsonResponse
    {
        $exists = $modelClass::where('user_id', $userId)
            ->where($foreignKey, $entityId)
            ->first();

        if ($exists) {
            $exists->delete();

            return response()->json(['status' => 'removed']);
        }

        $modelClass::create([
            'user_id' => $userId,
            $foreignKey => $entityId,
        ]);

        return response()->json(['status' => 'added']);
    }

    public function updateList(string $modelClass, string $foreignKey, int $userId, int $entityId, ?string $category): JsonResponse
    {
        if (! $category || $category === 'null') {
            $modelClass::where('user_id', $userId)
                ->where($foreignKey, $entityId)
                ->delete();

            return response()->json(['status' => 'ok', 'category' => null]);
        }

        $fav = $modelClass::updateOrCreate(
            ['user_id' => $userId, $foreignKey => $entityId],
            ['category' => $category]
        );

        return response()->json(['status' => 'ok', 'category' => $fav->category]);
    }

    public function myList(string $modelClass, int $userId, ?string $activeCategory, array $categories, string $withRelation, int $perPage = 24)
    {
        $query = $modelClass::where('user_id', $userId)
            ->with($withRelation);

        if ($activeCategory && array_key_exists($activeCategory, $categories)) {
            $query->where('category', $activeCategory);
        } elseif ($activeCategory === 'favorites') {
            $query->whereNull('category');
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }
}
