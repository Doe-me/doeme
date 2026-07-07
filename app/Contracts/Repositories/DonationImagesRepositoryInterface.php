<?php

namespace App\Contracts\Repositories;

use App\Models\DonationImages;

interface DonationImagesRepositoryInterface
{
    public function create(array $data): DonationImages;

    public function findById(int $id): ?DonationImages;

    public function delete(DonationImages $image): bool;

    public function deleteByItemId(int $donationItemId): bool;

    public function countByItemId(int $donationItemId): int;
}
