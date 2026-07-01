<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\User;

class ChatPolicy
{
    public function view(User $user, Chat $chat): bool
    {
        return $chat->hasUser($user->id);
    }

    public function sendMessage(User $user, Chat $chat): bool
    {
        return $chat->hasUser($user->id);
    }
}
