<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\DonationItem;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================
    // DonationItem policies
    // =========================================================

    public function test_non_owner_cannot_update_donation_item(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $item = DonationItem::factory()->create([
            'user_id' => $owner->id,
            'condition' => 'Novo',
        ]);

        $response = $this->actingAs($other)->putJson("/api/donation-items/{$item->id}", [
            'title' => 'Novo título',
        ]);

        $response->assertStatus(403);
    }

    public function test_non_owner_cannot_delete_donation_item(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $item = DonationItem::factory()->create([
            'user_id' => $owner->id,
            'condition' => 'Novo',
        ]);

        $response = $this->actingAs($other)->deleteJson("/api/donation-items/{$item->id}");

        $response->assertStatus(403);
    }

    public function test_owner_can_update_own_donation_item(): void
    {
        $owner = User::factory()->create();
        $item = DonationItem::factory()->create([
            'user_id' => $owner->id,
            'condition' => 'Novo',
        ]);

        $response = $this->actingAs($owner)->putJson("/api/donation-items/{$item->id}", [
            'title' => 'Novo título',
            'description' => $item->description,
            'category_id' => $item->category_id,
            'condition' => 'Novo',
            'location' => $item->location,
        ]);

        $response->assertStatus(200);
    }

    // =========================================================
    // Chat policies
    // =========================================================

    public function test_non_participant_cannot_view_chat(): void
    {
        $donor = User::factory()->create();
        $interested = User::factory()->create();
        $outsider = User::factory()->create();

        $chat = Chat::factory()->create([
            'donor_id' => $donor->id,
            'interested_user_id' => $interested->id,
        ]);

        $response = $this->actingAs($outsider)->getJson("/api/chats/{$chat->id}");

        $response->assertStatus(403);
    }

    public function test_participant_can_view_chat(): void
    {
        $donor = User::factory()->create();
        $interested = User::factory()->create();

        $chat = Chat::factory()->create([
            'donor_id' => $donor->id,
            'interested_user_id' => $interested->id,
        ]);

        $response = $this->actingAs($donor)->getJson("/api/chats/{$chat->id}");

        $response->assertStatus(200);
    }

    // =========================================================
    // Review policies
    // =========================================================

    public function test_non_reviewer_cannot_update_review(): void
    {
        $reviewer = User::factory()->create();
        $other = User::factory()->create();
        $review = Review::factory()->create(['reviewer_id' => $reviewer->id]);

        $response = $this->actingAs($other)->putJson("/api/reviews/{$review->id}", [
            'rating' => 3,
        ]);

        $response->assertStatus(403);
    }

    public function test_non_reviewer_cannot_delete_review(): void
    {
        $reviewer = User::factory()->create();
        $other = User::factory()->create();
        $review = Review::factory()->create(['reviewer_id' => $reviewer->id]);

        $response = $this->actingAs($other)->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(403);
    }
}
