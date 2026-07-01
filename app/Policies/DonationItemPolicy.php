<?php

namespace App\Policies;

use App\Models\DonationItem;
use App\Models\User;

class DonationItemPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, DonationItem $donationItem): bool
    {
        return $user->id === $donationItem->user_id;
    }

    public function delete(User $user, DonationItem $donationItem): bool
    {
        return $user->id === $donationItem->user_id;
    }
}
