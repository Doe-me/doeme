<?php

namespace Tests\Feature;

use App\Models\DonationItem;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_is_public_and_ok(): void
    {
        $this->getJson('/api/health')
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonStructure(['status', 'timestamp']);
    }

    public function test_user_review_stats_endpoint_returns_stats(): void
    {
        $user = User::factory()->create();
        Review::factory()->count(2)->create(['reviewed_user_id' => $user->id, 'rating' => 5]);

        $this->getJson("/api/users/{$user->id}/review-stats")
            ->assertStatus(200)
            ->assertJsonStructure(['total_reviews', 'average_rating', 'rating_distribution'])
            ->assertJsonPath('total_reviews', 2);
    }

    public function test_can_review_returns_true_for_eligible_reviewer(): void
    {
        $donor = User::factory()->create();
        $recipient = User::factory()->create();
        $item = DonationItem::factory()->donated()->create([
            'user_id' => $donor->id,
            'donated_to_user_id' => $recipient->id,
        ]);
        $token = $donor->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson("/api/reviews/can-review?user_id={$recipient->id}&donation_item_id={$item->id}")
            ->assertStatus(200)
            ->assertJsonPath('can_review', true);
    }

    public function test_can_review_returns_false_when_item_not_donated(): void
    {
        $donor = User::factory()->create();
        $other = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $donor->id, 'status' => 'available']);
        $token = $donor->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson("/api/reviews/can-review?user_id={$other->id}&donation_item_id={$item->id}")
            ->assertStatus(200)
            ->assertJsonPath('can_review', false);
    }

    public function test_can_review_requires_authentication(): void
    {
        $this->getJson('/api/reviews/can-review?user_id=1&donation_item_id=1')
            ->assertStatus(401);
    }
}
