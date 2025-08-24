<?php

namespace App\Services;

use App\Contracts\Services\CategoryServiceInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CategoryService implements CategoryServiceInterface
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    public function getAllActive(): Collection
    {
        return $this->categoryRepository->getAllActive();
    }

    public function getById(int $id): ?Category
    {
        return $this->categoryRepository->findById($id);
    }

    public function create(array $data): Category
    {
        // Gerar slug automaticamente se não fornecido
        if (!isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Definir como ativa por padrão
        if (!isset($data['active'])) {
            $data['active'] = true;
        }

        return $this->categoryRepository->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        // Atualizar slug se o nome foi alterado
        if (isset($data['name']) && $data['name'] !== $category->name) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $this->categoryRepository->update($category, $data);
    }

    public function delete(Category $category): bool
    {
        if (!$this->canDelete($category)) {
            throw new \Exception('Não é possível excluir esta categoria pois ela possui itens associados.');
        }

        return $this->categoryRepository->delete($category);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->categoryRepository->paginate($perPage);
    }

    public function canDelete(Category $category): bool
    {
        return !$this->categoryRepository->isInUse($category);
    }
}

