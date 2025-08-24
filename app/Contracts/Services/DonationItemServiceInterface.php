<?php

namespace App\Contracts\Services;

use App\Models\DonationItem;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DonationItemServiceInterface
{
    /**
     * Listar itens disponíveis com filtros
     */
    public function getAvailableItems(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Obter item por ID
     */
    public function getById(int $id): ?DonationItem;

    /**
     * Criar novo item de doação
     */
    public function create(User $user, array $data): DonationItem;

    /**
     * Atualizar item de doação
     */
    public function update(DonationItem $item, User $user, array $data): DonationItem;

    /**
     * Excluir item de doação
     */
    public function delete(DonationItem $item, User $user): bool;

    /**
     * Listar itens de um usuário
     */
    public function getUserItems(User $user, int $perPage = 15): LengthAwarePaginator;

    /**
     * Buscar itens por localização
     */
    public function findByLocation(float $latitude, float $longitude, int $radius = 10, int $perPage = 15): LengthAwarePaginator;

    /**
     * Marcar item como doado
     */
    public function markAsDonated(DonationItem $item, User $donor, User $recipient): DonationItem;

    /**
     * Obter itens relacionados
     */
    public function getRelatedItems(DonationItem $item, int $limit = 5): \Illuminate\Database\Eloquent\Collection;

    /**
     * Verificar se usuário pode modificar item
     */
    public function canUserModify(DonationItem $item, User $user): bool;
}

