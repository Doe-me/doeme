<?php

namespace App\Repositories;

use App\Contracts\Repositories\ChatRepositoryInterface;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use App\Models\DonationItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ChatRepository implements ChatRepositoryInterface
{
    public function create(array $data): Chat
    {
        return Chat::create($data);
    }

    public function findById(int $id): ?Chat
    {
        return Chat::with(['donationItem', 'donor', 'interestedUser'])->find($id);
    }

    public function findOrCreateChat(DonationItem $item, User $donor, User $interestedUser): Chat
    {
        $chat = Chat::where('donation_item_id', $item->id)
            ->where('donor_id', $donor->id)
            ->where('interested_user_id', $interestedUser->id)
            ->first();

        if (!$chat) {
            $chat = $this->create([
                'donation_item_id' => $item->id,
                'donor_id' => $donor->id,
                'interested_user_id' => $interestedUser->id,
                'last_message_at' => now(),
            ]);
        }

        return $chat->load(['donationItem', 'donor', 'interestedUser']);
    }

    public function getUserChats(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return Chat::with(['donationItem', 'donor', 'interestedUser', 'lastMessage'])
            ->where(function($query) use ($user) {
                $query->where('donor_id', $user->id)
                      ->orWhere('interested_user_id', $user->id);
            })
            ->orderBy('last_message_at', 'desc')
            ->paginate($perPage);
    }

    public function addMessage(Chat $chat, User $user, string $message): ChatMessage
    {
        $chatMessage = ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
            'message' => $message,
        ]);

        $this->updateLastMessageAt($chat);

        return $chatMessage->load('user');
    }

    public function getChatMessages(Chat $chat, int $perPage = 50): LengthAwarePaginator
    {
        return $chat->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);
    }

    public function markMessagesAsRead(Chat $chat, User $user): int
    {
        return $chat->messages()
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function userParticipatesInChat(Chat $chat, User $user): bool
    {
        return $chat->donor_id === $user->id || $chat->interested_user_id === $user->id;
    }

    public function updateLastMessageAt(Chat $chat): Chat
    {
        $chat->update(['last_message_at' => now()]);
        return $chat;
    }
}

