<?php

namespace Tests\Feature;

use App\Contracts\Services\DonationItemServiceInterface;
use App\Models\DonationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ServerErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'SQLSTATE[HY000] internal db detail 42xyz';

    public function test_unexpected_error_returns_generic_message_without_leaking(): void
    {
        Log::spy();

        $this->mock(DonationItemServiceInterface::class, function ($mock) {
            $mock->shouldReceive('getAvailableItems')
                ->andThrow(new \RuntimeException(self::SECRET));
        });

        $response = $this->getJson('/api/donation-items');

        $response->assertStatus(500)
            ->assertJson([
                'error' => 'Erro interno do servidor',
                'message' => 'Ocorreu um erro inesperado. Tente novamente mais tarde.',
            ]);

        // A mensagem interna nunca chega ao cliente...
        $this->assertStringNotContainsString(self::SECRET, $response->getContent());
        // ...mas é logada para diagnóstico interno.
        Log::shouldHaveReceived('error')->once();
    }

    public function test_update_server_error_returns_500_not_403(): void
    {
        $owner = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $owner->id]);
        $token = $owner->createToken('test')->plainTextToken;

        $this->mock(DonationItemServiceInterface::class, function ($mock) {
            $mock->shouldReceive('update')->andThrow(new \RuntimeException(self::SECRET));
        });

        $response = $this->withToken($token)->putJson("/api/donation-items/{$item->id}", [
            'title' => 'Novo título',
        ]);

        $response->assertStatus(500);
        $this->assertStringNotContainsString(self::SECRET, $response->getContent());
    }

    public function test_non_owner_update_still_returns_403(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $owner->id]);
        $token = $intruder->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->putJson("/api/donation-items/{$item->id}", [
            'title' => 'Novo título',
        ]);

        $response->assertStatus(403);
    }

    public function test_deleting_a_donated_item_returns_422_domain_error(): void
    {
        $owner = User::factory()->create();
        $recipient = User::factory()->create();
        $item = DonationItem::factory()->donated()->create([
            'user_id' => $owner->id,
            'donated_to_user_id' => $recipient->id,
        ]);
        $token = $owner->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->deleteJson("/api/donation-items/{$item->id}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('donation_items', ['id' => $item->id]);
    }
}
