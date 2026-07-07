<?php

namespace App\Contracts\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Laravel\Socialite\Contracts\User as SocialiteUser;

interface AuthServiceInterface
{
    /**
     * Registrar novo usuário
     */
    public function register(array $data): array;

    /**
     * Fazer login do usuário
     */
    public function login(array $credentials): array;

    /**
     * Fazer logout do usuário
     */
    public function logout(User $user): bool;

    /**
     * Atualizar perfil do usuário
     */
    public function updateProfile(User $user, array $data): User;

    /**
     * Processar login social
     */
    public function handleSocialLogin(string $provider, SocialiteUser $socialUser): array;

    /**
     * Atualizar endereço do usuário
     */
    public function updateAddress(User $user, array $data): User;

    /**
     * Alterar senha do usuário
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): void;

    /**
     * Enviar link de recuperação de senha por e-mail
     */
    public function sendPasswordResetLink(string $email): string;

    /**
     * Redefinir a senha a partir do token de recuperação
     */
    public function resetPassword(array $data): string;

    /**
     * Gerar token de acesso
     */
    public function generateAccessToken(User $user, string $tokenName = 'auth_token'): string;

    /**
     * Revogar todos os tokens do usuário
     */
    public function revokeAllTokens(User $user): bool;

    public function updateAvatar(User $user, UploadedFile $file): User;

    public function updateNotificationPreferences(User $user, array $data): User;

    public function updatePrivacySettings(User $user, array $data): User;

    public function deleteAccount(User $user, string $password): void;
}
