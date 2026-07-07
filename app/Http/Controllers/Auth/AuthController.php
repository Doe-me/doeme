<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\Services\AuthServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\DeleteAccountRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateAddressRequest;
use App\Http\Requests\Auth\UpdateNotificationPreferencesRequest;
use App\Http\Requests\Auth\UpdatePrivacySettingsRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Endpoints para autenticação de usuários"
 * )
 */
class AuthController extends Controller
{
    public function __construct(
        private AuthServiceInterface $authService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Registrar novo usuário",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *
     *             @OA\Property(property="name", type="string", example="João Silva"),
     *             @OA\Property(property="email", type="string", format="email", example="joao@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *             @OA\Property(property="phone", type="string", example="(11) 99999-9999"),
     *             @OA\Property(property="location", type="string", example="São Paulo, SP")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Usuário registrado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Usuário registrado com sucesso"),
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(property="token", type="string", example="1|abc123..."),
     *             @OA\Property(property="token_type", type="string", example="Bearer")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());

            return response()->json([
                'message' => 'Usuário registrado com sucesso',
                'user' => $result['user'],
                'token' => $result['token'],
                'token_type' => $result['token_type'],
            ], 201);
        } catch (\Exception $e) {
            return $this->serverError($e, 'auth');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Fazer login",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email","password"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="joao@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login realizado com sucesso",
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
     *         response=422,
     *         description="Credenciais inválidas",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());

            return response()->json([
                'message' => 'Login realizado com sucesso',
                'user' => $result['user'],
                'token' => $result['token'],
                'token_type' => $result['token_type'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Credenciais inválidas',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Fazer logout",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Logout realizado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Logout realizado com sucesso")
     *         )
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());

            return response()->json([
                'message' => 'Logout realizado com sucesso',
            ]);
        } catch (\Exception $e) {
            return $this->serverError($e, 'auth');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/auth/user",
     *     summary="Obter dados do usuário autenticado",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Dados do usuário",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     )
     * )
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/auth/profile",
     *     summary="Atualizar perfil do usuário",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="name", type="string", example="João Silva"),
     *             @OA\Property(property="email", type="string", format="email", example="joao@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="phone", type="string", example="(11) 99999-9999"),
     *             @OA\Property(property="location", type="string", example="São Paulo, SP"),
     *             @OA\Property(property="avatar", type="string", example="https://example.com/avatar.jpg")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Perfil atualizado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Perfil atualizado com sucesso"),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->updateProfile($request->user(), $request->validated());

            return response()->json([
                'message' => 'Perfil atualizado com sucesso',
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            return $this->serverError($e, 'auth');
        }
    }

    public function updateAddress(UpdateAddressRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->updateAddress($request->user(), $request->validated());

            return response()->json([
                'message' => 'Endereço atualizado com sucesso',
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            return $this->serverError($e, 'auth');
        }
    }

    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate(['avatar' => ['required', 'image', 'max:2048']]);

        try {
            $user = $this->authService->updateAvatar($request->user(), $request->file('avatar'));

            return response()->json(['message' => 'Avatar atualizado com sucesso', 'user' => $user]);
        } catch (\Exception $e) {
            return $this->serverError($e, 'auth.update_avatar');
        }
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->changePassword(
                $request->user(),
                $request->input('current_password'),
                $request->input('password')
            );

            return response()->json(['message' => 'Senha alterada com sucesso']);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Erro de validação', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return $this->serverError($e, 'auth');
        }
    }

    public function getNotificationPreferences(Request $request): JsonResponse
    {
        $defaults = [
            'email_new_message' => true,
            'email_donation_interest' => true,
            'email_newsletter' => false,
            'push_new_message' => true,
            'push_donation_interest' => true,
            'push_new_reviews' => false,
        ];

        $prefs = array_merge($defaults, $request->user()->notification_preferences ?? []);

        return response()->json(['notification_preferences' => $prefs]);
    }

    public function updateNotificationPreferences(UpdateNotificationPreferencesRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->updateNotificationPreferences($request->user(), $request->validated());

            return response()->json(['message' => 'Preferências atualizadas', 'notification_preferences' => $user->notification_preferences]);
        } catch (\Exception $e) {
            return $this->serverError($e, 'auth');
        }
    }

    public function getPrivacySettings(Request $request): JsonResponse
    {
        $defaults = [
            'show_email' => false,
            'show_phone' => false,
            'show_location' => true,
            'allow_messages' => true,
            'online_status' => true,
        ];

        $settings = array_merge($defaults, $request->user()->privacy_settings ?? []);

        return response()->json(['privacy_settings' => $settings]);
    }

    public function updatePrivacySettings(UpdatePrivacySettingsRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->updatePrivacySettings($request->user(), $request->validated());

            return response()->json(['message' => 'Configurações atualizadas', 'privacy_settings' => $user->privacy_settings]);
        } catch (\Exception $e) {
            return $this->serverError($e, 'auth');
        }
    }

    public function getConnectedAccounts(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'connected_accounts' => [
                'google' => ! empty($user->google_id),
                'facebook' => ! empty($user->facebook_id),
            ],
        ]);
    }

    public function deleteAccount(DeleteAccountRequest $request): JsonResponse
    {
        try {
            $this->authService->deleteAccount($request->user(), $request->input('password'));

            return response()->json(['message' => 'Conta excluída com sucesso']);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Erro de validação', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return $this->serverError($e, 'auth');
        }
    }
}
