<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Contracts\Services\ChatServiceInterface;
use App\Http\Requests\Api\StoreChatRequest;
use App\Http\Requests\Api\SendMessageRequest;
use App\Models\Chat;
use App\Models\DonationItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Chat",
 *     description="Endpoints para gerenciamento de chats"
 * )
 */
class ChatController extends Controller
{
    public function __construct(
        private ChatServiceInterface $chatService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/chats",
     *     summary="Listar meus chats",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         @OA\Schema(type="integer", example=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de chats do usuário",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="donation_item", ref="#/components/schemas/DonationItem"),
     *                 @OA\Property(property="donor", ref="#/components/schemas/User"),
     *                 @OA\Property(property="interested_user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="last_message_at", type="string", format="date-time")
     *             ))
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 20);
            $chats = $this->chatService->getUserChats($request->user(), $perPage);

            return response()->json($chats);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/chats",
     *     summary="Iniciar novo chat",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"donation_item_id","message"},
     *             @OA\Property(property="donation_item_id", type="integer", example=1),
     *             @OA\Property(property="message", type="string", example="Olá, tenho interesse neste item!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Chat iniciado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Chat iniciado com sucesso"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="donation_item", ref="#/components/schemas/DonationItem"),
     *                 @OA\Property(property="donor", ref="#/components/schemas/User"),
     *                 @OA\Property(property="interested_user", ref="#/components/schemas/User")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao iniciar chat",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function store(StoreChatRequest $request): JsonResponse
    {
        try {
            $donationItem = DonationItem::findOrFail($request->donation_item_id);
            $chat = $this->chatService->startChat(
                $donationItem,
                $request->user(),
                $request->message
            );

            return response()->json([
                'message' => 'Chat iniciado com sucesso',
                'data' => $chat
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao iniciar chat',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/chats/{id}",
     *     summary="Obter mensagens do chat",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         @OA\Schema(type="integer", example=50)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mensagens do chat",
     *         @OA\JsonContent(
     *             @OA\Property(property="chat", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="donation_item", ref="#/components/schemas/DonationItem"),
     *                 @OA\Property(property="donor", ref="#/components/schemas/User"),
     *                 @OA\Property(property="interested_user", ref="#/components/schemas/User")
     *             ),
     *             @OA\Property(property="messages", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="message", type="string"),
     *                     @OA\Property(property="user", ref="#/components/schemas/User"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="read_at", type="string", format="date-time")
     *                 ))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Sem permissão para acessar este chat",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show(Chat $chat, Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 50);
            $messages = $this->chatService->getChatMessages($chat, $request->user(), $perPage);

            return response()->json([
                'chat' => $chat,
                'messages' => $messages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao acessar chat',
                'message' => $e->getMessage()
            ], 403);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/chats/{id}/messages",
     *     summary="Enviar mensagem",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message"},
     *             @OA\Property(property="message", type="string", example="Quando posso buscar o item?")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Mensagem enviada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Mensagem enviada com sucesso"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="message", type="string"),
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Sem permissão para enviar mensagem neste chat",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function sendMessage(SendMessageRequest $request, Chat $chat): JsonResponse
    {
        try {
            $message = $this->chatService->sendMessage($chat, $request->user(), $request->message);

            return response()->json([
                'message' => 'Mensagem enviada com sucesso',
                'data' => $message
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao enviar mensagem',
                'message' => $e->getMessage()
            ], 403);
        }
    }
}

