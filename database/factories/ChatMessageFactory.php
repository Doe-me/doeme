<?php

namespace Database\Factories;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChatMessage>
 */
class ChatMessageFactory extends Factory
{
    protected $model = ChatMessage::class;

    public function definition(): array
    {
        return [
            'chat_id' => Chat::factory(),
            'user_id' => User::factory(),
            'message' => $this->faker->sentence(),
            'read_at' => null,
        ];
    }
}
