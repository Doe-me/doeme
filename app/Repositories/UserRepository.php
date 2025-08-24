<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findBySocialId(string $provider, string $socialId): ?User
    {
        $column = $provider . '_id';
        return User::where($column, $socialId)->first();
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return User::latest()->paginate($perPage);
    }

    public function getUserStats(User $user): array
    {
        return [
            'total_donations' => $user->donationItems()->count(),
            'total_received' => $user->receivedItems()->count(),
            'average_rating' => $user->average_rating,
            'total_reviews' => $user->total_reviews,
            'active_chats' => $user->donorChats()
                ->orWhere(function($query) use ($user) {
                    $query->where('interested_user_id', $user->id);
                })
                ->count(),
            'completed_donations' => $user->donationItems()
                ->where('status', 'donated')
                ->count(),
        ];
    }
}

