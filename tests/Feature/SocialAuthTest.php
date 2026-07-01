<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider as SocialiteProviderContract;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    private function mockSocialiteUser(array $attributes = []): SocialiteUser
    {
        $socialiteUser = new SocialiteUser();
        $socialiteUser->map(array_merge([
            'id' => '123456789',
            'name' => 'Doador Google',
            'email' => 'doador.google@example.com',
            'avatar' => 'https://example.com/avatar.jpg',
        ], $attributes));

        return $socialiteUser;
    }

    public function test_redirect_to_provider_uses_stateless_mode(): void
    {
        $provider = \Mockery::mock(SocialiteProviderContract::class);
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('redirect')->once()->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $response = $this->get('/api/auth/google/redirect');

        $response->assertStatus(302);
        $response->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_callback_creates_user_and_redirects_to_frontend_with_token(): void
    {
        $provider = \Mockery::mock(SocialiteProviderContract::class);
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn($this->mockSocialiteUser());

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $response = $this->get('/api/auth/google/callback');

        $response->assertStatus(302);
        $location = $response->headers->get('Location');

        $this->assertStringStartsWith(config('app.frontend_url').'/auth/google/callback', $location);
        $this->assertStringContainsString('token=', $location);

        $this->assertDatabaseHas('users', [
            'email' => 'doador.google@example.com',
            'google_id' => '123456789',
        ]);
    }

    public function test_callback_links_social_id_to_existing_user_with_same_email(): void
    {
        $existingUser = User::factory()->create(['email' => 'ja.cadastrado@example.com']);

        $provider = \Mockery::mock(SocialiteProviderContract::class);
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn(
            $this->mockSocialiteUser(['email' => 'ja.cadastrado@example.com'])
        );

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $this->get('/api/auth/google/callback');

        $this->assertDatabaseHas('users', [
            'id' => $existingUser->id,
            'email' => 'ja.cadastrado@example.com',
            'google_id' => '123456789',
        ]);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_callback_redirects_to_frontend_with_error_when_user_denies_permission(): void
    {
        $response = $this->get('/api/auth/google/callback?error=access_denied&error_description=The+user+denied+access');

        $response->assertStatus(302);
        $location = $response->headers->get('Location');

        $this->assertStringStartsWith(config('app.frontend_url').'/auth/google/callback', $location);
        $this->assertStringContainsString('error=', $location);
        $this->assertStringContainsString('denied+access', $location);
    }

    public function test_callback_redirects_to_frontend_with_error_when_socialite_throws(): void
    {
        $provider = \Mockery::mock(SocialiteProviderContract::class);
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('user')->once()->andThrow(new \Exception('Invalid code'));

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $response = $this->get('/api/auth/google/callback');

        $response->assertStatus(302);
        $location = $response->headers->get('Location');

        $this->assertStringStartsWith(config('app.frontend_url').'/auth/google/callback', $location);
        $this->assertStringContainsString('error=', $location);
    }
}
