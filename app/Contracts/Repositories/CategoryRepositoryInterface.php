<?php

namespace App\Contracts\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
{
    /**
     * Criar uma nova categoria
     */
    public function create(array $data): Category;

    /**
     * Encontrar categoria por ID
     */
    public function findById(int $id): ?Category;

    /**
     * Encontrar categoria por slug
     */
    public function findBySlug(string $slug): ?Category;

    /**
     * Atualizar categoria
     */
    public function update(Category $category, array $data): Category;

    /**
     * Excluir categoria
     */
    public function delete(Category $category): bool;

    /**
     * Listar todas as categorias ativas
     */
    public function getAllActive(): Collection;

    /**
     * Listar categorias com paginação
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Verificar se categoria está em uso
     */
    public function isInUse(Category $category): bool;
}

