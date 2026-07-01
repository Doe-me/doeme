<?php

namespace App\Repositories;

use App\Contracts\Repositories\DonationImagesRepositoryInterface;
use App\Models\DonationImages;

class DonationImagesRepository implements DonationImagesRepositoryInterface
{
    public function __construct(private DonationImages $model) {}

    public function create(array $data): DonationImages
    {
        return $this->model::create($data);
    }

    public function findById(int $id): ?DonationImages
    {
        return $this->model::find($id);
    }

    public function deleteByItemId(int $donationItemId): bool
    {
        return $this->model->where('donation_item_id', $donationItemId)->delete() > 0;
    }

    public function countByItemId(int $donationItemId): int
    {
        return $this->model->where('donation_item_id', $donationItemId)->count();
    }
}
