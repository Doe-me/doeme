<?php

namespace App\Services;

use App\Contracts\Repositories\DonationItemRepositoryInterface;
use App\Contracts\Services\DonationItemServiceInterface;
use App\Models\DonationImages;
use App\Models\DonationItem;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class DonationItemService implements DonationItemServiceInterface
{
    public function __construct(
        private DonationItemRepositoryInterface $donationItemRepository
    ) {}

    public function getAvailableItems(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->donationItemRepository->getAvailableItems($filters, $perPage);
    }

    public function getById(int $id): ?DonationItem
    {
        return $this->donationItemRepository->findById($id);
    }

    public function create(User $user, array $data): DonationItem
    {
        $data['user_id'] = $user->id;
        $data['status'] = 'available';

        return $this->donationItemRepository->create($data);
    }

    public function update(DonationItem $item, User $user, array $data): DonationItem
    {
        if (! $this->canUserModify($item, $user)) {
            throw new \Exception('Você não tem permissão para modificar este item.');
        }

        // Não permitir alterar status diretamente
        unset($data['status'], $data['user_id']);

        return $this->donationItemRepository->update($item, $data);
    }

    public function delete(DonationItem $item, User $user): bool
    {
        if (! $this->canUserModify($item, $user)) {
            throw new \Exception('Você não tem permissão para excluir este item.');
        }

        if ($item->status === 'donated') {
            throw new \Exception('Não é possível excluir um item que já foi doado.');
        }

        // Delete physical image files before removing the item (FK cascade handles DB records)
        $images = DonationImages::where('donation_item_id', $item->id)->get();
        foreach ($images as $img) {
            Storage::disk('public')->delete($img->path);
        }

        return $this->donationItemRepository->delete($item);
    }

    public function getUserItems(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->donationItemRepository->getUserItems($user, $perPage);
    }

    public function findByLocation(float $latitude, float $longitude, float $radius = 10, int $perPage = 15): LengthAwarePaginator
    {
        return $this->donationItemRepository->findByLocation($latitude, $longitude, $radius, $perPage);
    }

    public function markAsDonated(DonationItem $item, User $donor, User $recipient): DonationItem
    {
        if (! $this->canUserModify($item, $donor)) {
            throw new \Exception('Você não tem permissão para marcar este item como doado.');
        }

        if ($item->status !== 'available') {
            throw new \Exception('Este item não está disponível para doação.');
        }

        return $this->donationItemRepository->markAsDonated($item, $recipient);
    }

    public function getRelatedItems(DonationItem $item, int $limit = 5): Collection
    {
        return $this->donationItemRepository->getRelatedItems($item, $limit);
    }

    public function canUserModify(DonationItem $item, User $user): bool
    {
        return $item->user_id === $user->id;
    }
}
