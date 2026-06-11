<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository
{
    public function getAllByUser(int $userId): Collection
    {
        return Category::where('user_id', $userId)->get();
    }

    public function getAllByUserPaginated(int $userId, int $perPage = 10): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Category::where('user_id', $userId)
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findByIdAndUser(int $id, int $userId): ?Category
    {
        return Category::where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function firstOrCreate(int $userId, string $name): Category
    {
        return Category::firstOrCreate(
            ['user_id' => $userId, 'name' => $name]
        );
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);
        return $category;
    }

    public function delete(Category $category): bool
    {
        return $category->delete();
    }
}