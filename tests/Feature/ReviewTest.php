<?php

namespace Tests\Feature;

use App\Models\DonationItem;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_list_reviews(): void
    {
        $response = $this->getJson('/api/reviews');

        $response->assertStatus(200);
    }

    public function test_anyone_can_list_reviews_by_user(): void
    {
        $user = User::factory()->create();
        Review::factory()->create(['reviewed_user_id' => $user->id]);

        $response = $this->getJson("/api/users/{$user->id}/reviews");

        $response->assertStatus(200);
    }

    public function test_donor_can_review_recipient_after_donation(): void
    {
        $donor = User::factory()->create();
        $recipient = User::factory()->create();

        $item = DonationItem::factory()->create([
            'user_id' => $donor->id,
            'status' => 'donated',
            'donated_to_user_id' => $recipient->id,
            'donated_at' => now(),
        ]);

        $token = $donor->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/reviews', [
            'donation_item_id' => $item->id,
            'reviewed_user_id' => $recipient->id,
            'rating' => 5,
            'comment' => 'Ótima experiência!',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data']);
    }

    public function test_recipient_can_review_donor_after_donation(): void
    {
        $donor = User::factory()->create();
        $recipient = User::factory()->create();

        $item = DonationItem::factory()->create([
            'user_id' => $donor->id,
            'status' => 'donated',
            'donated_to_user_id' => $recipient->id,
            'donated_at' => now(),
        ]);

        $token = $recipient->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/reviews', [
            'donation_item_id' => $item->id,
            'reviewed_user_id' => $donor->id,
            'rating' => 4,
            'comment' => 'Muito gentil!',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data']);
    }

    public function test_user_cannot_review_without_donation_relation(): void
    {
        $outsider = User::factory()->create();
        $donor = User::factory()->create();
        $recipient = User::factory()->create();

        $item = DonationItem::factory()->create([
            'user_id' => $donor->id,
            'status' => 'available',
        ]);

        $token = $outsider->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/reviews', [
            'donation_item_id' => $item->id,
            'reviewed_user_id' => $donor->id,
            'rating' => 3,
        ]);

        // Service throws Exception which controller returns as 400
        $response->assertStatus(400);
    }
}
