<?php

namespace App\Contracts\Services;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use App\Models\DonationItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ChatServiceInterface
{
    /**
     * Listar chats do usuário
     */
    public function getUserChats(User $user, int $perPage = 20): LengthAwarePaginator;

    /**
     * Obter chat por ID
     */
    public function getById(int $id): ?Chat;

    /**
     * Iniciar novo chat
     */
    public function startChat(DonationItem $item, User $interestedUser, string $message): Chat;

    /**
     * Obter mensagens do chat
     */
    public function getChatMessages(Chat $chat, User $user, int $perPage = 50): LengthAwarePaginator;

    /**
     * Enviar mensagem
     */
    public function sendMessage(Chat $chat, User $user, string $message): ChatMessage;

    /**
     * Marcar mensagens como lidas
     */
    public function markMessagesAsRead(Chat $chat, User $user): int;

    /**
     * Verificar se usuário pode acessar chat
     */
    public function canUserAccess(Chat $chat, User $user): bool;
}

