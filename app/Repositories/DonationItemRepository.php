<?php

namespace App\Repositories;

use App\Contracts\Repositories\DonationItemRepositoryInterface;
use App\Models\DonationItem;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class DonationItemRepository implements DonationItemRepositoryInterface
{
    public function create(array $data): DonationItem
    {
        return DonationItem::create($data);
    }

    public function findById(int $id): ?DonationItem
    {
        return DonationItem::with(['user', 'category', 'reviews.reviewer'])->find($id);
    }

    public function update(DonationItem $item, array $data): DonationItem
    {
        $item->update($data);
        return $item->fresh(['user', 'category']);
    }

    public function delete(DonationItem $item): bool
    {
        return $item->delete();
    }

    public function getAvailableItems(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DonationItem::with(['user', 'category', 'donationImages'])
            ->available()
            ->latest();

        // Aplicar filtros
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['location'])) {
            $query->where('location', 'like', '%' . $filters['location'] . '%');
        }

        if (isset($filters['condition'])) {
            $query->where('condition', $filters['condition']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        return $query->paginate($perPage);
    }

    public function getUserItems(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return DonationItem::with(['category'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    public function findByLocation(float $latitude, float $longitude, int $radius = 10, int $perPage = 15): LengthAwarePaginator
    {
        return DonationItem::with(['user', 'category'])
            ->available()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->selectRaw("
                *,
                (6371 * acos(cos(radians(?)) 
                * cos(radians(latitude)) 
                * cos(radians(longitude) - radians(?)) 
                + sin(radians(?)) 
                * sin(radians(latitude)))) AS distance
            ", [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radius)
            ->orderBy('distance')
            ->paginate($perPage);
    }

    public function findByCategory(int $categoryId, int $perPage = 15): LengthAwarePaginator
    {
        return DonationItem::with(['user', 'category'])
            ->available()
            ->where('category_id', $categoryId)
            ->latest()
            ->paginate($perPage);
    }

    public function markAsDonated(DonationItem $item, User $recipient): DonationItem
    {
        $item->update([
            'status' => 'donated',
            'donated_at' => now(),
            'donated_to_user_id' => $recipient->id,
        ]);

        return $item->fresh(['user', 'category', 'donatedToUser']);
    }

    public function getRelatedItems(DonationItem $item, int $limit = 5): Collection
    {
        return DonationItem::with(['user', 'category'])
            ->available()
            ->where('id', '!=', $item->id)
            ->where('category_id', $item->category_id)
            ->limit($limit)
            ->get();
    }
}

