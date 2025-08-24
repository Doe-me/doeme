<?php

namespace App\Services;

use App\Contracts\Repositories\DonationImagesRepositoryInterface;
use App\Contracts\Services\DonationItemServiceInterface;
use App\Contracts\Repositories\DonationItemRepositoryInterface;
use App\Models\DonationItem;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class DonationItemService implements DonationItemServiceInterface
{
    public function __construct(
        private DonationItemRepositoryInterface $donationItemRepository,
        private DonationImagesRepositoryInterface $donationImagesRepository,
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
        $data['address'] = data_get($data, 'location.address');
        $data['latitude'] = data_get($data, 'location.latitude');
        $data['longitude'] = data_get($data, 'location.longitude');

        $donation =  $this->donationItemRepository->create($data);

        $disk = env('IMAGE_DISK');
        foreach (data_get($data, 'images') as $image) {
            $name = $image->getClientOriginalName();

            $path = $image->storeAs(
                'images',
                $name,
                $disk
            );

            $this->donationImagesRepository->create([
                'donation_item_id' => $donation->id,
                'path' => $path,
            ]);
        }

        return $donation;
    }

    public function update(DonationItem $item, User $user, array $data): DonationItem
    {
        if (!$this->canUserModify($item, $user)) {
            throw new \Exception('Você não tem permissão para modificar este item.');
        }

        // Não permitir alterar status diretamente
        unset($data['status'], $data['user_id']);

        return $this->donationItemRepository->update($item, $data);
    }

    public function delete(DonationItem $item, User $user): bool
    {
        if (!$this->canUserModify($item, $user)) {
            throw new \Exception('Você não tem permissão para excluir este item.');
        }

        if ($item->status === 'donated') {
            throw new \Exception('Não é possível excluir um item que já foi doado.');
        }

        return $this->donationItemRepository->delete($item);
    }

    public function getUserItems(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->donationItemRepository->getUserItems($user, $perPage);
    }

    public function findByLocation(float $latitude, float $longitude, int $radius = 10, int $perPage = 15): LengthAwarePaginator
    {
        return $this->donationItemRepository->findByLocation($latitude, $longitude, $radius, $perPage);
    }

    public function markAsDonated(DonationItem $item, User $donor, User $recipient): DonationItem
    {
        if (!$this->canUserModify($item, $donor)) {
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
