<?php

namespace App\Contracts\Repositories;

use App\Models\DonationItem;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DonationItemRepositoryInterface
{
    /**
     * Criar um novo item de doação
     */
    public function create(array $data): DonationItem;

    /**
     * Encontrar item por ID
     */
    public function findById(int $id): ?DonationItem;

    /**
     * Atualizar item
     */
    public function update(DonationItem $item, array $data): DonationItem;

    /**
     * Excluir item
     */
    public function delete(DonationItem $item): bool;

    /**
     * Listar itens disponíveis com filtros
     */
    public function getAvailableItems(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Listar itens de um usuário
     */
    public function getUserItems(User $user, int $perPage = 15): LengthAwarePaginator;

    /**
     * Buscar itens por localização
     */
    public function findByLocation(float $latitude, float $longitude, int $radius = 10, int $perPage = 15): LengthAwarePaginator;

    /**
     * Buscar itens por categoria
     */
    public function findByCategory(int $categoryId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Marcar item como doado
     */
    public function markAsDonated(DonationItem $item, User $recipient): DonationItem;

    /**
     * Obter itens relacionados
     */
    public function getRelatedItems(DonationItem $item, int $limit = 5): \Illuminate\Database\Eloquent\Collection;
}

