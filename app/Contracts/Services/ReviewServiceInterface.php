<?php

namespace App\Contracts\Services;

use App\Models\Review;
use App\Models\User;
use App\Models\DonationItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReviewServiceInterface
{
    /**
     * Listar avaliações com filtros
     */
    public function getReviews(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Obter avaliação por ID
     */
    public function getById(int $id): ?Review;

    /**
     * Criar nova avaliação
     */
    public function create(User $reviewer, array $data): Review;

    /**
     * Atualizar avaliação
     */
    public function update(Review $review, User $user, array $data): Review;

    /**
     * Excluir avaliação
     */
    public function delete(Review $review, User $user): bool;

    /**
     * Obter avaliações de um usuário
     */
    public function getUserReviews(User $user, int $perPage = 15): array;

    /**
     * Verificar se usuário pode avaliar
     */
    public function canUserReview(DonationItem $item, User $reviewer, User $reviewedUser): bool;

    /**
     * Verificar se usuário pode modificar avaliação
     */
    public function canUserModify(Review $review, User $user): bool;
}

