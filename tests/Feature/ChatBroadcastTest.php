<?php

namespace Tests\Feature;

use App\Events\MessageSent;
use App\Models\Chat;
use App\Models\DonationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ChatBroadcastTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // O driver "log" (padrão em .env.testing, para não disparar broadcast real
        // em testes que não usam Event::fake) nunca verifica autorização de canal.
        // Trocamos para o driver real (Pusher-compatible) só nestes testes e
        // re-registramos os canais nele, já que Broadcast::channel() os registra
        // no driver resolvido como default no momento do boot da aplicação.
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'test-key',
            'broadcasting.connections.reverb.secret' => 'test-secret',
            'broadcasting.connections.reverb.app_id' => 'test-app',
        ]);
        require base_path('routes/channels.php');
    }

    public function test_sending_a_message_dispatches_message_sent_event(): void
    {
        Event::fake([MessageSent::class]);

        $donor = User::factory()->create();
        $interestedUser = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $donor->id]);
        $chat = Chat::factory()->create([
            'donation_item_id' => $item->id,
            'donor_id' => $donor->id,
            'interested_user_id' => $interestedUser->id,
        ]);

        $response = $this->actingAs($interestedUser, 'sanctum')
            ->postJson("/api/chats/{$chat->id}/messages", [
                'message' => 'Olá, ainda está disponível?',
            ]);

        $response->assertStatus(201);

        Event::assertDispatched(MessageSent::class, function (MessageSent $event) use ($chat) {
            return $event->message->chat_id === $chat->id
                && $event->message->message === 'Olá, ainda está disponível?';
        });
    }

    public function test_message_sent_event_broadcasts_on_private_chat_channel(): void
    {
        $donor = User::factory()->create();
        $interestedUser = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $donor->id]);
        $chat = Chat::factory()->create([
            'donation_item_id' => $item->id,
            'donor_id' => $donor->id,
            'interested_user_id' => $interestedUser->id,
        ]);
        $message = $chat->messages()->create([
            'user_id' => $interestedUser->id,
            'message' => 'Teste de canal',
        ]);

        $event = new MessageSent($message);
        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertSame('private-chat.'.$chat->id, $channels[0]->name);
    }

    public function test_chat_participant_can_authorize_private_channel(): void
    {
        $donor = User::factory()->create();
        $interestedUser = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $donor->id]);
        $chat = Chat::factory()->create([
            'donation_item_id' => $item->id,
            'donor_id' => $donor->id,
            'interested_user_id' => $interestedUser->id,
        ]);

        $response = $this->actingAs($interestedUser, 'sanctum')
            ->postJson('/broadcasting/auth', [
                'channel_name' => 'private-chat.'.$chat->id,
                'socket_id' => '1234.5678',
            ]);

        $response->assertStatus(200);
    }

    public function test_user_outside_chat_cannot_authorize_private_channel(): void
    {
        $donor = User::factory()->create();
        $interestedUser = User::factory()->create();
        $outsider = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $donor->id]);
        $chat = Chat::factory()->create([
            'donation_item_id' => $item->id,
            'donor_id' => $donor->id,
            'interested_user_id' => $interestedUser->id,
        ]);

        $response = $this->actingAs($outsider, 'sanctum')
            ->postJson('/broadcasting/auth', [
                'channel_name' => 'private-chat.'.$chat->id,
                'socket_id' => '1234.5678',
            ]);

        $response->assertStatus(403);
    }
}
