<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Disable throttling for auth routes to avoid 429 in tests
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'user', 'token', 'token_type']);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create(['password' => bcrypt('Password123!')]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'user', 'token', 'token_type']);
    }

    public function test_invalid_credentials_return_422(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/auth/logout');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/auth/user');

        $response->assertStatus(200)
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_user_can_upload_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->post('/api/auth/avatar', [
            'avatar' => UploadedFile::fake()->create('avatar.png', 100, 'image/png'),
        ]);

        $response->assertStatus(200)->assertJsonPath('message', 'Avatar atualizado com sucesso');
        $this->assertNotNull($response->json('user.avatar'));
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('OldPassword123!')]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/auth/change-password', [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(200)->assertJsonPath('message', 'Senha alterada com sucesso');
    }

    public function test_change_password_rejects_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('CorrectPassword123!')]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/auth/change-password', [
            'current_password' => 'WrongPassword!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(422)->assertJsonPath('errors.current_password.0', 'A senha atual está incorreta.');
    }

    public function test_user_can_get_notification_preferences(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/auth/notification-preferences');

        $response->assertStatus(200)->assertJsonStructure(['notification_preferences']);
    }

    public function test_user_can_update_notification_preferences(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->putJson('/api/auth/notification-preferences', [
            'email_new_message' => false,
            'email_newsletter' => true,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_user_can_get_privacy_settings(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/auth/privacy-settings');

        $response->assertStatus(200)->assertJsonStructure(['privacy_settings']);
    }

    public function test_user_can_update_privacy_settings(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->putJson('/api/auth/privacy-settings', [
            'show_email' => true,
            'allow_messages' => false,
        ]);

        $response->assertStatus(200);
    }

    public function test_user_can_get_connected_accounts(): void
    {
        $user = User::factory()->create(['google_id' => 'google123']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/auth/connected-accounts');

        $response->assertStatus(200)
            ->assertJsonPath('connected_accounts.google', true)
            ->assertJsonPath('connected_accounts.facebook', false);
    }

    public function test_user_can_delete_account(): void
    {
        $user = User::factory()->create(['password' => bcrypt('Password123!')]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->deleteJson('/api/auth/account', [
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200);
        $this->assertNull(User::find($user->id));
    }

    public function test_delete_account_rejects_wrong_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('CorrectPassword123!')]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->deleteJson('/api/auth/account', [
            'password' => 'WrongPassword!',
        ]);

        $response->assertStatus(422);
        $this->assertNotNull(User::find($user->id));
    }
}
