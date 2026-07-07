<?php

namespace App\Repositories;

use App\Contracts\Repositories\DonationItemRepositoryInterface;
use App\Models\DonationItem;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator as ManualPaginator;
use Illuminate\Pagination\Paginator;

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
        $query = DonationItem::with(['user', 'category'])
            ->available()
            ->latest();

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['location'])) {
            $query->where('location', 'like', '%'.$filters['location'].'%');
        }

        if (isset($filters['condition'])) {
            $query->where('condition', $filters['condition']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        // Filtro por raio geográfico: bounding box em SQL, distância exata em PHP
        if (isset($filters['latitude'], $filters['longitude'])) {
            $lat = (float) $filters['latitude'];
            $lon = (float) $filters['longitude'];
            $radius = (float) ($filters['radius'] ?? 10);

            $query->nearLocation($lat, $lon, $radius);
        }

        return $query->paginate($perPage);
    }

    public function getUserItems(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return DonationItem::with(['category', 'donatedToUser'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Busca itens por raio geográfico com distância exata via Haversine em PHP.
     *
     * O bounding box em SQL reduz drasticamente os candidatos; o filtro exato e
     * a ordenação por distância são feitos em PHP para funcionar igual em SQLite
     * (dev) e PostgreSQL (produção), sem depender de extensões espaciais.
     */
    public function findByLocation(float $latitude, float $longitude, float $radius = 10, int $perPage = 15): LengthAwarePaginator
    {
        $candidates = DonationItem::with(['user', 'category'])
            ->available()
            ->nearLocation($latitude, $longitude, (float) $radius)
            ->get();

        $withDistance = $candidates
            ->map(function (DonationItem $item) use ($latitude, $longitude) {
                $item->distance = $this->haversineKm(
                    $latitude, $longitude,
                    (float) $item->latitude, (float) $item->longitude
                );

                return $item;
            })
            ->filter(fn (DonationItem $item) => $item->distance <= $radius)
            ->sortBy('distance')
            ->values();

        $page = Paginator::resolveCurrentPage();
        $total = $withDistance->count();
        $items = $withDistance->slice(($page - 1) * $perPage, $perPage)->values();

        return new ManualPaginator($items, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
        ]);
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

    /**
     * Distância em km entre dois pontos via fórmula de Haversine.
     */
    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $earthRadius * 2 * asin(sqrt($a));
    }
}
