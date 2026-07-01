<?php

namespace Database\Factories;

use App\Models\Chat;
use App\Models\DonationItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chat>
 */
class ChatFactory extends Factory
{
    protected $model = Chat::class;

    public function definition(): array
    {
        return [
            'donation_item_id' => DonationItem::factory(),
            'donor_id' => User::factory(),
            'interested_user_id' => User::factory(),
            'last_message_at' => null,
        ];
    }
}
