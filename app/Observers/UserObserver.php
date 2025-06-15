<?php

namespace App\Observers;

use App\Models\User;
use App\Notifications\WelcomeNotification;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Enviar notificação de boas-vindas para o novo usuário
        $user->notify(new WelcomeNotification());
    }
}
