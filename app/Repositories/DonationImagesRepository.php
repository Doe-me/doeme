<?php

namespace App\Repositories;

use App\Contracts\Repositories\DonationImagesRepositoryInterface;
use App\Models\DonationImages;

class DonationImagesRepository implements DonationImagesRepositoryInterface
{
    public function __construct(private DonationImages $model)
    {
    }

    public function create(array $data): DonationImages
    {
        return $this->model::create($data);
    }

    public function findById(int $id): ?DonationImages
    {
        return $this->model::find($id);
    }

    public function delete(int $id): bool
    {
        return $this->model->where('id', $id)->delete();
    }

}

