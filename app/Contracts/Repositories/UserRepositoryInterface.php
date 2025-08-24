<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    /**
     * Criar um novo usuário
     */
    public function create(array $data): User;

    /**
     * Encontrar usuário por ID
     */
    public function findById(int $id): ?User;

    /**
     * Encontrar usuário por email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Encontrar usuário por ID do provedor social
     */
    public function findBySocialId(string $provider, string $socialId): ?User;

    /**
     * Atualizar usuário
     */
    public function update(User $user, array $data): User;

    /**
     * Excluir usuário
     */
    public function delete(User $user): bool;

    /**
     * Listar usuários com paginação
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Obter estatísticas do usuário
     */
    public function getUserStats(User $user): array;
}

