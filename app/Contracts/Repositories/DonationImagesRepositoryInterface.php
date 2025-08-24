<?php

namespace App\Contracts\Repositories;

use App\Models\DonationImages;

interface DonationImagesRepositoryInterface
{
    /**
     * Criar um nova imagem
     */
    public function create(array $data): DonationImages;

    /**
     * Encontrar imagem por ID
     */
    public function findById(int $id): ?DonationImages;

    /**
     * Excluir imagem
     */
    public function delete(int $id): bool;
}

