<?php

namespace App\Observers;

use App\Models\Board;
use App\Events\BoardUpdated;

class BoardObserver
{
    /**
     * Handle the Board "created" event.
     */
    public function created(Board $board): void
    {
        broadcast(new BoardUpdated($board, 'created'));
    }

    /**
     * Handle the Board "updated" event.
     */
    public function updated(Board $board): void
    {
        broadcast(new BoardUpdated($board, 'updated'));
    }

    /**
     * Handle the Board "deleted" event.
     */
    public function deleted(Board $board): void
    {
        broadcast(new BoardUpdated($board, 'deleted'));
    }
}
