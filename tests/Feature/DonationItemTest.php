<?php

namespace Tests\Feature;

use App\Models\DonationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonationItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_list_donation_items(): void
    {
        DonationItem::factory()->count(3)->create();

        $response = $this->getJson('/api/donation-items');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_anyone_can_view_a_donation_item(): void
    {
        $item = DonationItem::factory()->create();

        $response = $this->getJson("/api/donation-items/{$item->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'related_items']);
    }

    public function test_authenticated_user_can_create_donation_item(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $item = DonationItem::factory()->make(['user_id' => $user->id]);

        $response = $this->withToken($token)->postJson('/api/donation-items', [
            'title' => $item->title,
            'description' => $item->description,
            'category_id' => $item->category_id,
            'condition' => $item->condition,
            'location' => $item->location,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data']);
    }

    public function test_owner_can_update_donation_item(): void
    {
        $owner = User::factory()->create();
        $token = $owner->createToken('test')->plainTextToken;
        $item = DonationItem::factory()->create(['user_id' => $owner->id]);

        $response = $this->withToken($token)->putJson("/api/donation-items/{$item->id}", [
            'title' => 'Updated Title',
            'description' => $item->description,
            'category_id' => $item->category_id,
            'condition' => $item->condition,
            'location' => $item->location,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data']);
    }

    public function test_owner_can_delete_donation_item(): void
    {
        $owner = User::factory()->create();
        $token = $owner->createToken('test')->plainTextToken;
        $item = DonationItem::factory()->create(['user_id' => $owner->id]);

        $response = $this->withToken($token)->deleteJson("/api/donation-items/{$item->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);
    }

    public function test_non_owner_cannot_update_donation_item(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $token = $other->createToken('test')->plainTextToken;
        $item = DonationItem::factory()->create(['user_id' => $owner->id]);

        $response = $this->withToken($token)->putJson("/api/donation-items/{$item->id}", [
            'title' => 'Hacked Title',
            'description' => $item->description,
            'category_id' => $item->category_id,
            'condition' => $item->condition,
            'location' => $item->location,
        ]);

        $response->assertStatus(403);
    }

    public function test_non_owner_cannot_delete_donation_item(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $token = $other->createToken('test')->plainTextToken;
        $item = DonationItem::factory()->create(['user_id' => $owner->id]);

        $response = $this->withToken($token)->deleteJson("/api/donation-items/{$item->id}");

        $response->assertStatus(403);
    }

    public function test_authenticated_user_can_list_own_donations(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        DonationItem::factory()->count(2)->create(['user_id' => $user->id]);

        $response = $this->withToken($token)->getJson('/api/my-donations');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
