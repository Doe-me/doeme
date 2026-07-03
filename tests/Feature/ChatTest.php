<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\DonationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_start_a_chat(): void
    {
        $donor = User::factory()->create();
        $interested = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $donor->id, 'status' => 'available']);

        $token = $interested->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/chats', [
            'donation_item_id' => $item->id,
            'message' => 'Olá, tenho interesse neste item!',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data']);
    }

    public function test_user_cannot_start_chat_with_own_item(): void
    {
        $donor = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $donor->id, 'status' => 'available']);

        $token = $donor->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/chats', [
            'donation_item_id' => $item->id,
            'message' => 'Tentando conversar com meu próprio item.',
        ]);

        $response->assertStatus(400);
    }

    public function test_user_can_list_own_chats(): void
    {
        $donor = User::factory()->create();
        $interested = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $donor->id]);

        Chat::create([
            'donation_item_id' => $item->id,
            'donor_id' => $donor->id,
            'interested_user_id' => $interested->id,
        ]);

        $token = $interested->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/chats');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_participant_can_view_chat_messages(): void
    {
        $donor = User::factory()->create();
        $interested = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $donor->id]);

        $chat = Chat::create([
            'donation_item_id' => $item->id,
            'donor_id' => $donor->id,
            'interested_user_id' => $interested->id,
        ]);

        $token = $interested->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson("/api/chats/{$chat->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['chat', 'messages']);
    }

    public function test_participant_can_send_message(): void
    {
        $donor = User::factory()->create();
        $interested = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $donor->id]);

        $chat = Chat::create([
            'donation_item_id' => $item->id,
            'donor_id' => $donor->id,
            'interested_user_id' => $interested->id,
        ]);

        $token = $donor->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson("/api/chats/{$chat->id}/messages", [
            'message' => 'Pode buscar amanhã!',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data']);
    }

    public function test_non_participant_cannot_view_chat(): void
    {
        $donor = User::factory()->create();
        $interested = User::factory()->create();
        $outsider = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $donor->id]);

        $chat = Chat::create([
            'donation_item_id' => $item->id,
            'donor_id' => $donor->id,
            'interested_user_id' => $interested->id,
        ]);

        $token = $outsider->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson("/api/chats/{$chat->id}");

        $response->assertStatus(403);
    }

    public function test_non_participant_cannot_send_message(): void
    {
        $donor = User::factory()->create();
        $interested = User::factory()->create();
        $outsider = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $donor->id]);

        $chat = Chat::create([
            'donation_item_id' => $item->id,
            'donor_id' => $donor->id,
            'interested_user_id' => $interested->id,
        ]);

        $token = $outsider->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson("/api/chats/{$chat->id}/messages", [
            'message' => 'Intruso tentando mandar mensagem.',
        ]);

        $response->assertStatus(403);
    }
}
