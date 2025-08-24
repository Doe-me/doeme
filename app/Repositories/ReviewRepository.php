<?php

namespace App\Repositories;

use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Models\Review;
use App\Models\User;
use App\Models\DonationItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewRepository implements ReviewRepositoryInterface
{
    public function create(array $data): Review
    {
        return Review::create($data);
    }

    public function findById(int $id): ?Review
    {
        return Review::with(['reviewer', 'reviewedUser', 'donationItem'])->find($id);
    }

    public function update(Review $review, array $data): Review
    {
        $review->update($data);
        return $review->fresh(['reviewer', 'reviewedUser', 'donationItem']);
    }

    public function delete(Review $review): bool
    {
        return $review->delete();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Review::with(['reviewer', 'reviewedUser', 'donationItem'])
            ->latest();

        if (isset($filters['reviewed_user_id'])) {
            $query->where('reviewed_user_id', $filters['reviewed_user_id']);
        }

        if (isset($filters['reviewer_id'])) {
            $query->where('reviewer_id', $filters['reviewer_id']);
        }

        if (isset($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }

        return $query->paginate($perPage);
    }

    public function getUserReviews(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Review::with(['reviewer', 'donationItem'])
            ->where('reviewed_user_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    public function reviewExists(DonationItem $item, User $reviewer, User $reviewedUser): bool
    {
        return Review::where('donation_item_id', $item->id)
            ->where('reviewer_id', $reviewer->id)
            ->where('reviewed_user_id', $reviewedUser->id)
            ->exists();
    }

    public function canUserReview(DonationItem $item, User $reviewer, User $reviewedUser): bool
    {
        // Verificar se o item foi doado
        if ($item->status !== 'donated') {
            return false;
        }

        // Verificar se o reviewer participou da doação
        $participatedInDonation = $item->user_id === $reviewer->id || 
                                 $item->donated_to_user_id === $reviewer->id;

        if (!$participatedInDonation) {
            return false;
        }

        // Verificar se já não existe uma avaliação
        if ($this->reviewExists($item, $reviewer, $reviewedUser)) {
            return false;
        }

        // Não pode avaliar a si mesmo
        if ($reviewer->id === $reviewedUser->id) {
            return false;
        }

        return true;
    }

    public function getUserReviewStats(User $user): array
    {
        $reviews = Review::where('reviewed_user_id', $user->id);

        return [
            'total_reviews' => $reviews->count(),
            'average_rating' => round($reviews->avg('rating'), 1),
            'rating_distribution' => [
                '5' => $reviews->where('rating', 5)->count(),
                '4' => $reviews->where('rating', 4)->count(),
                '3' => $reviews->where('rating', 3)->count(),
                '2' => $reviews->where('rating', 2)->count(),
                '1' => $reviews->where('rating', 1)->count(),
            ]
        ];
    }
}

