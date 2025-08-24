<?php

namespace App\Contracts\Repositories;

use App\Models\Review;
use App\Models\User;
use App\Models\DonationItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReviewRepositoryInterface
{
    /**
     * Criar uma nova avaliação
     */
    public function create(array $data): Review;

    /**
     * Encontrar avaliação por ID
     */
    public function findById(int $id): ?Review;

    /**
     * Atualizar avaliação
     */
    public function update(Review $review, array $data): Review;

    /**
     * Excluir avaliação
     */
    public function delete(Review $review): bool;

    /**
     * Listar avaliações com paginação
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Obter avaliações de um usuário
     */
    public function getUserReviews(User $user, int $perPage = 15): LengthAwarePaginator;

    /**
     * Verificar se já existe avaliação
     */
    public function reviewExists(DonationItem $item, User $reviewer, User $reviewedUser): bool;

    /**
     * Verificar se usuário pode avaliar
     */
    public function canUserReview(DonationItem $item, User $reviewer, User $reviewedUser): bool;

    /**
     * Obter estatísticas de avaliações de um usuário
     */
    public function getUserReviewStats(User $user): array;
}

