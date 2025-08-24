<?php

namespace App\Contracts\Repositories;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use App\Models\DonationItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ChatRepositoryInterface
{
    /**
     * Criar um novo chat
     */
    public function create(array $data): Chat;

    /**
     * Encontrar chat por ID
     */
    public function findById(int $id): ?Chat;

    /**
     * Encontrar ou criar chat entre usuários para um item
     */
    public function findOrCreateChat(DonationItem $item, User $donor, User $interestedUser): Chat;

    /**
     * Listar chats de um usuário
     */
    public function getUserChats(User $user, int $perPage = 20): LengthAwarePaginator;

    /**
     * Adicionar mensagem ao chat
     */
    public function addMessage(Chat $chat, User $user, string $message): ChatMessage;

    /**
     * Obter mensagens do chat
     */
    public function getChatMessages(Chat $chat, int $perPage = 50): LengthAwarePaginator;

    /**
     * Marcar mensagens como lidas
     */
    public function markMessagesAsRead(Chat $chat, User $user): int;

    /**
     * Verificar se usuário participa do chat
     */
    public function userParticipatesInChat(Chat $chat, User $user): bool;

    /**
     * Atualizar timestamp da última mensagem
     */
    public function updateLastMessageAt(Chat $chat): Chat;
}

