<?php

use App\Models\Chat;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// A API é stateless (Sanctum via Bearer token), então cada canal precisa indicar
// explicitamente o guard "sanctum" - sem isso, retrieveUser() cai no guard "web"
// padrão e nunca encontra o usuário autenticado por token.
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['sanctum']]);

// Canal privado de um chat: só os dois participantes (doador e interessado) podem ouvir.
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);

    return $chat !== null && $chat->hasUser($user->id);
}, ['guards' => ['sanctum']]);
