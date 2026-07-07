<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // O link de recuperação aponta para a tela do frontend (SPA), não para
        // uma rota web do Laravel. O front lê token+email da query e chama
        // POST /api/auth/reset-password.
        ResetPassword::createUrlUsing(function (object $notifiable, string $token): string {
            $base = rtrim(config('app.frontend_url'), '/');
            $email = urlencode($notifiable->getEmailForPasswordReset());

            return "{$base}/reset-password?token={$token}&email={$email}";
        });
    }
}
