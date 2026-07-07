<?php

namespace Tests\Feature;

use App\Models\DonationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonationCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_mark_item_as_donated(): void
    {
        $donor = User::factory()->create();
        $recipient = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $donor->id, 'status' => 'available']);
        $token = $donor->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson("/api/donation-items/{$item->id}/donate", ['recipient_id' => $recipient->id]);

        $response->assertStatus(200)->assertJsonPath('data.status', 'donated');
        $this->assertDatabaseHas('donation_items', [
            'id' => $item->id,
            'status' => 'donated',
            'donated_to_user_id' => $recipient->id,
        ]);
    }

    public function test_can_donate_a_reserved_item(): void
    {
        $donor = User::factory()->create();
        $recipient = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $donor->id, 'status' => 'reserved']);
        $token = $donor->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson("/api/donation-items/{$item->id}/donate", ['recipient_id' => $recipient->id]);

        $response->assertStatus(200)->assertJsonPath('data.status', 'donated');
    }

    public function test_non_owner_cannot_mark_as_donated(): void
    {
        $donor = User::factory()->create();
        $recipient = User::factory()->create();
        $intruder = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $donor->id, 'status' => 'available']);
        $token = $intruder->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson("/api/donation-items/{$item->id}/donate", ['recipient_id' => $recipient->id]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('donation_items', ['id' => $item->id, 'status' => 'available']);
    }

    public function test_cannot_donate_to_self(): void
    {
        $donor = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $donor->id, 'status' => 'available']);
        $token = $donor->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson("/api/donation-items/{$item->id}/donate", ['recipient_id' => $donor->id]);

        $response->assertStatus(403);
    }

    public function test_cannot_donate_an_already_donated_item(): void
    {
        $donor = User::factory()->create();
        $recipient = User::factory()->create();
        $item = DonationItem::factory()->donated()->create([
            'user_id' => $donor->id,
            'donated_to_user_id' => $recipient->id,
        ]);
        $token = $donor->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson("/api/donation-items/{$item->id}/donate", ['recipient_id' => $recipient->id]);

        $response->assertStatus(403);
    }

    public function test_donate_requires_valid_recipient(): void
    {
        $donor = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $donor->id, 'status' => 'available']);
        $token = $donor->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson("/api/donation-items/{$item->id}/donate", ['recipient_id' => 999999]);

        $response->assertStatus(422);
    }

    public function test_owner_can_reserve_and_cancel_reservation(): void
    {
        $donor = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $donor->id, 'status' => 'available']);
        $token = $donor->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson("/api/donation-items/{$item->id}/reserve")
            ->assertStatus(200)->assertJsonPath('data.status', 'reserved');

        $this->withToken($token)
            ->postJson("/api/donation-items/{$item->id}/cancel-reservation")
            ->assertStatus(200)->assertJsonPath('data.status', 'available');
    }

    public function test_non_owner_cannot_reserve(): void
    {
        $donor = User::factory()->create();
        $intruder = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $donor->id, 'status' => 'available']);
        $token = $intruder->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson("/api/donation-items/{$item->id}/reserve")
            ->assertStatus(403);
    }

    /**
     * O caso que o ticket existe para resolver: sem o endpoint de doação nenhuma
     * avaliação era possível. Aqui provamos o fluxo real ponta a ponta.
     */
    public function test_review_becomes_possible_after_donating_via_endpoint(): void
    {
        $donor = User::factory()->create();
        $recipient = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $donor->id, 'status' => 'available']);
        $token = $donor->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson("/api/donation-items/{$item->id}/donate", ['recipient_id' => $recipient->id])
            ->assertStatus(200);

        $review = $this->withToken($token)->postJson('/api/reviews', [
            'donation_item_id' => $item->id,
            'reviewed_user_id' => $recipient->id,
            'rating' => 5,
            'comment' => 'Recebedor pontual.',
        ]);

        $review->assertStatus(201);
    }
}
