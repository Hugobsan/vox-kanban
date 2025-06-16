<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
|--------------------------------------------------------------------------
| Board Channels
|--------------------------------------------------------------------------
|
| Canais privados para atualizações em tempo real dos boards
|
*/

Broadcast::channel('board.{boardId}', function ($user, $boardId) {
    // Verifica se o usuário tem acesso ao board
    return $user->boards()->where('boards.id', $boardId)->exists();
});
