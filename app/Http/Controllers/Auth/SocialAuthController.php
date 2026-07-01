<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\Services\AuthServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

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
     *
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string", enum={"google", "facebook"})
     *     ),
     *
     *     @OA\Response(
     *         response=302,
     *         description="Redirecionamento para o provedor OAuth"
     *     )
     * )
     */
    public function redirectToProvider(string $provider): RedirectResponse
    {
        // stateless(): rotas de API não têm sessão (middleware "web"), e o
        // Socialite usa a sessão por padrão para guardar o "state" do OAuth.
        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * @OA\Get(
     *     path="/api/auth/{provider}/callback",
     *     summary="Callback do provedor OAuth",
     *     tags={"Social Authentication"},
     *
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string", enum={"google", "facebook"})
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login social realizado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Login realizado com sucesso"),
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(property="token", type="string", example="1|abc123..."),
     *             @OA\Property(property="token_type", type="string", example="Bearer")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=302,
     *         description="Redireciona para o frontend com o token (sucesso) ou com a mensagem de erro"
     *     )
     * )
     */
    public function handleProviderCallback(string $provider, \Illuminate\Http\Request $request): RedirectResponse
    {
        // Usuário negou permissão ou o provider retornou erro antes de gerar o "code".
        if ($request->has('error')) {
            return $this->redirectToFrontendWithError(
                $provider,
                $request->string('error_description', $request->string('error'))->toString()
            );
        }

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
            $result = $this->authService->handleSocialLogin($provider, $socialUser);

            return redirect()->away(
                $this->frontendCallbackUrl($provider).'?'.http_build_query([
                    'token' => $result['token'],
                ])
            );
        } catch (\Exception $e) {
            return $this->redirectToFrontendWithError($provider, $e->getMessage());
        }
    }

    private function redirectToFrontendWithError(string $provider, string $message): RedirectResponse
    {
        return redirect()->away(
            $this->frontendCallbackUrl($provider).'?'.http_build_query([
                'error' => $message ?: 'Erro na autenticação social',
            ])
        );
    }

    private function frontendCallbackUrl(string $provider): string
    {
        return rtrim(config('app.frontend_url'), '/')."/auth/{$provider}/callback";
    }
}
