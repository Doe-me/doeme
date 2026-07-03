<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\DonationItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DonationItem>
 */
class DonationItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'images' => [],
            'condition' => fake()->randomElement(['Novo', 'Usado - Excelente estado', 'Usado - Bom estado', 'Usado - Estado regular']),
            'location' => fake()->city().', '.fake()->stateAbbr(),
            'latitude' => null,
            'longitude' => null,
            'status' => 'available',
            'donated_at' => null,
            'donated_to_user_id' => null,
        ];
    }

    public function withCoordinates(float $latitude, float $longitude): static
    {
        return $this->state(['latitude' => $latitude, 'longitude' => $longitude]);
    }

    public function donated(): static
    {
        return $this->state(['status' => 'donated', 'donated_at' => now()]);
    }
}
