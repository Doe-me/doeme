<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Contracts\Services\AuthServiceInterface;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * @OA\Tag(
 *     name="Social Authentication",
 *     description="Endpoints para autenticação social"
 * )
 */
class SocialAuthController extends Controller
{
    public function __construct(
        private AuthServiceInterface $authService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/auth/{provider}/redirect",
     *     summary="Redirecionar para provedor OAuth",
     *     tags={"Social Authentication"},
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", enum={"google", "facebook"})
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirecionamento para o provedor OAuth"
     *     )
     * )
     */
    public function redirectToProvider(string $provider): RedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * @OA\Get(
     *     path="/api/auth/{provider}/callback",
     *     summary="Callback do provedor OAuth",
     *     tags={"Social Authentication"},
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", enum={"google", "facebook"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login social realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login realizado com sucesso"),
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(property="token", type="string", example="1|abc123..."),
     *             @OA\Property(property="token_type", type="string", example="Bearer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro na autenticação social",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function handleProviderCallback(string $provider): JsonResponse
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            $result = $this->authService->handleSocialLogin($provider, $socialUser);

            return response()->json([
                'message' => 'Login realizado com sucesso',
                'user' => $result['user'],
                'token' => $result['token'],
                'token_type' => $result['token_type'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro na autenticação social',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}

