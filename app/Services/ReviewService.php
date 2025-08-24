<?php

namespace App\Services;

use App\Contracts\Services\ReviewServiceInterface;
use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Models\Review;
use App\Models\User;
use App\Models\DonationItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewService implements ReviewServiceInterface
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository
    ) {}

    public function getReviews(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->reviewRepository->paginate($filters, $perPage);
    }

    public function getById(int $id): ?Review
    {
        return $this->reviewRepository->findById($id);
    }

    public function create(User $reviewer, array $data): Review
    {
        $donationItem = DonationItem::findOrFail($data['donation_item_id']);
        $reviewedUser = User::findOrFail($data['reviewed_user_id']);

        if (!$this->canUserReview($donationItem, $reviewer, $reviewedUser)) {
            throw new \Exception('Você não pode avaliar este usuário para este item.');
        }

        $data['reviewer_id'] = $reviewer->id;

        $review = $this->reviewRepository->create($data);

        // Atualizar estatísticas do usuário avaliado
        $this->updateUserRatingStats($reviewedUser);

        return $review;
    }

    public function update(Review $review, User $user, array $data): Review
    {
        if (!$this->canUserModify($review, $user)) {
            throw new \Exception('Você não tem permissão para modificar esta avaliação.');
        }

        // Não permitir alterar IDs
        unset($data['reviewer_id'], $data['reviewed_user_id'], $data['donation_item_id']);

        $review = $this->reviewRepository->update($review, $data);

        // Atualizar estatísticas do usuário avaliado
        $this->updateUserRatingStats($review->reviewedUser);

        return $review;
    }

    public function delete(Review $review, User $user): bool
    {
        if (!$this->canUserModify($review, $user)) {
            throw new \Exception('Você não tem permissão para excluir esta avaliação.');
        }

        $reviewedUser = $review->reviewedUser;
        $result = $this->reviewRepository->delete($review);

        if ($result) {
            // Atualizar estatísticas do usuário avaliado
            $this->updateUserRatingStats($reviewedUser);
        }

        return $result;
    }

    public function getUserReviews(User $user, int $perPage = 15): array
    {
        $reviews = $this->reviewRepository->getUserReviews($user, $perPage);
        $stats = $this->reviewRepository->getUserReviewStats($user);

        return [
            'reviews' => $reviews,
            'stats' => $stats,
        ];
    }

    public function canUserReview(DonationItem $item, User $reviewer, User $reviewedUser): bool
    {
        return $this->reviewRepository->canUserReview($item, $reviewer, $reviewedUser);
    }

    public function canUserModify(Review $review, User $user): bool
    {
        return $review->reviewer_id === $user->id;
    }

    private function updateUserRatingStats(User $user): void
    {
        $stats = $this->reviewRepository->getUserReviewStats($user);
        
        $user->update([
            'average_rating' => $stats['average_rating'],
            'total_reviews' => $stats['total_reviews'],
        ]);
    }
}

