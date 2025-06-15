<?php

namespace App\Observers;

use App\Models\BoardUser;
use App\Notifications\BoardInvitationToExistingUser;

class BoardUserObserver
{
    /**
     * Handle the BoardUser "created" event.
     */
    public function created(BoardUser $boardUser): void
    {
        // Carrega as relações necessárias
        $boardUser->load(['user', 'board']);

        if ($boardUser->user) {
            $boardUser->user->notify(new BoardInvitationToExistingUser($boardUser));
        }
    }
}
