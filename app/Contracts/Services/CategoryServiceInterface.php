<?php

namespace App\Contracts\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryServiceInterface
{
    /**
     * Listar todas as categorias ativas
     */
    public function getAllActive(): Collection;

    /**
     * Obter categoria por ID
     */
    public function getById(int $id): ?Category;

    /**
     * Criar nova categoria
     */
    public function create(array $data): Category;

    /**
     * Atualizar categoria
     */
    public function update(Category $category, array $data): Category;

    /**
     * Excluir categoria
     */
    public function delete(Category $category): bool;

    /**
     * Listar categorias com paginação
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Verificar se categoria pode ser excluída
     */
    public function canDelete(Category $category): bool;
}

