<?php

namespace App\Services;

use App\Contracts\Services\AuthServiceInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function register(array $data): array
    {
        $data['password'] = Hash::make($data['password']);
        
        $user = $this->userRepository->create($data);
        $token = $this->generateAccessToken($user, 'registration_token');

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function login(array $credentials): array
    {
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        $user = Auth::user();
        $token = $this->generateAccessToken($user, 'login_token');

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function logout(User $user): bool
    {
        return $this->revokeAllTokens($user);
    }

    public function updateProfile(User $user, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->userRepository->update($user, $data);
    }

    public function handleSocialLogin(string $provider, SocialiteUser $socialUser): array
    {
        // Tentar encontrar usuário por ID social
        $user = $this->userRepository->findBySocialId($provider, $socialUser->getId());

        if (!$user) {
            // Tentar encontrar por email
            $user = $this->userRepository->findByEmail($socialUser->getEmail());
            
            if ($user) {
                // Atualizar com dados sociais
                $socialData = [
                    $provider . '_id' => $socialUser->getId(),
                ];
                $user = $this->userRepository->update($user, $socialData);
            } else {
                // Criar novo usuário
                $userData = [
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'avatar' => $socialUser->getAvatar(),
                    $provider . '_id' => $socialUser->getId(),
                    'email_verified_at' => now(),
                ];
                
                $user = $this->userRepository->create($userData);
            }
        }

        $token = $this->generateAccessToken($user, $provider . '_token');

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function generateAccessToken(User $user, string $tokenName = 'auth_token'): string
    {
        return $user->createToken($tokenName)->plainTextToken;
    }

    public function revokeAllTokens(User $user): bool
    {
        $user->tokens()->delete();
        return true;
    }
}

