<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Throttling não é o foco aqui; o limiter é compartilhado entre os testes.
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_forgot_password_sends_reset_notification(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'jane@example.com']);

        $response = $this->postJson('/api/auth/forgot-password', ['email' => 'jane@example.com']);

        $response->assertStatus(200);
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_returns_generic_response_for_unknown_email(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/auth/forgot-password', ['email' => 'ghost@example.com']);

        // Resposta genérica (não revela que o e-mail não existe)
        $response->assertStatus(200);
        Notification::assertNothingSent();
    }

    public function test_forgot_password_validates_email(): void
    {
        $this->postJson('/api/auth/forgot-password', ['email' => 'not-an-email'])
            ->assertStatus(422);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create(['email' => 'jane@example.com']);
        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => 'jane@example.com',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertStatus(200);
        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_reset_password_fails_with_invalid_token(): void
    {
        User::factory()->create(['email' => 'jane@example.com']);

        $response = $this->postJson('/api/auth/reset-password', [
            'token' => 'totally-invalid-token',
            'email' => 'jane@example.com',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_login_with_new_password_after_reset(): void
    {
        $user = User::factory()->create([
            'email' => 'jane@example.com',
            'password' => Hash::make('old-password'),
        ]);
        $token = Password::createToken($user);

        $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => 'jane@example.com',
            'password' => 'brand-new-pass-1',
            'password_confirmation' => 'brand-new-pass-1',
        ])->assertStatus(200);

        $this->postJson('/api/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'brand-new-pass-1',
        ])->assertStatus(200)->assertJsonStructure(['token']);
    }
}
