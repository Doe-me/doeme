<?php

namespace App\Services;

use App\Contracts\Services\ChatServiceInterface;
use App\Contracts\Repositories\ChatRepositoryInterface;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use App\Models\DonationItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ChatService implements ChatServiceInterface
{
    public function __construct(
        private ChatRepositoryInterface $chatRepository
    ) {}

    public function getUserChats(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $this->chatRepository->getUserChats($user, $perPage);
    }

    public function getById(int $id): ?Chat
    {
        return $this->chatRepository->findById($id);
    }

    public function startChat(DonationItem $item, User $interestedUser, string $message): Chat
    {
        if ($item->user_id === $interestedUser->id) {
            throw new \Exception('Você não pode iniciar um chat com seu próprio item.');
        }

        if ($item->status !== 'available') {
            throw new \Exception('Este item não está mais disponível.');
        }

        $chat = $this->chatRepository->findOrCreateChat($item, $item->user, $interestedUser);
        
        // Adicionar mensagem inicial
        $this->chatRepository->addMessage($chat, $interestedUser, $message);

        return $chat;
    }

    public function getChatMessages(Chat $chat, User $user, int $perPage = 50): LengthAwarePaginator
    {
        if (!$this->canUserAccess($chat, $user)) {
            throw new \Exception('Você não tem permissão para acessar este chat.');
        }

        // Marcar mensagens como lidas
        $this->chatRepository->markMessagesAsRead($chat, $user);

        return $this->chatRepository->getChatMessages($chat, $perPage);
    }

    public function sendMessage(Chat $chat, User $user, string $message): ChatMessage
    {
        if (!$this->canUserAccess($chat, $user)) {
            throw new \Exception('Você não tem permissão para enviar mensagens neste chat.');
        }

        return $this->chatRepository->addMessage($chat, $user, $message);
    }

    public function markMessagesAsRead(Chat $chat, User $user): int
    {
        if (!$this->canUserAccess($chat, $user)) {
            throw new \Exception('Você não tem permissão para acessar este chat.');
        }

        return $this->chatRepository->markMessagesAsRead($chat, $user);
    }

    public function canUserAccess(Chat $chat, User $user): bool
    {
        return $this->chatRepository->userParticipatesInChat($chat, $user);
    }
}

