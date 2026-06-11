<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Support\ServiceReturn;

class CategoryService
{
    public function __construct(
        protected CategoryRepository $categoryRepository
    ) {}

    public function getAll(int $userId): ServiceReturn
    {
        $categories = $this->categoryRepository->getAllByUser(userId: $userId);
        return ServiceReturn::success(data: $categories);
    }

    public function create(int $userId, string $name): ServiceReturn
    {
        $category = $this->categoryRepository->firstOrCreate(userId: $userId, name: $name);
        return ServiceReturn::success(data: $category, status: 201);
    }

    public function update(int $id, int $userId, string $name): ServiceReturn
    {
        $category = $this->categoryRepository->findByIdAndUser(id: $id, userId: $userId);
        if (!$category) {
            return ServiceReturn::error(message: 'Category not found', status: 404);
        }
        $category = $this->categoryRepository->update(category: $category, data: ['name' => $name]);
        return ServiceReturn::success(data: $category);
    }

    public function delete(int $id, int $userId): ServiceReturn
    {
        $category = $this->categoryRepository->findByIdAndUser(id: $id, userId: $userId);
        if (!$category) {
            return ServiceReturn::error(message: 'Category not found', status: 404);
        }
        $this->categoryRepository->delete(category: $category);
        return ServiceReturn::success(message: 'Category deleted');
    }
}