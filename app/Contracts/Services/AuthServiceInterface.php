<?php

namespace App\Contracts\Services;

use App\Models\User;
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
     * Gerar token de acesso
     */
    public function generateAccessToken(User $user, string $tokenName = 'auth_token'): string;

    /**
     * Revogar todos os tokens do usuário
     */
    public function revokeAllTokens(User $user): bool;
}

