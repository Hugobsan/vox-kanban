<?php

namespace App\Observers;

use App\Models\Column;
use App\Events\ColumnUpdated;

class ColumnObserver
{
    /**
     * Handle the Column "created" event.
     */
    public function created(Column $column): void
    {
        broadcast(new ColumnUpdated($column, 'created'));
    }

    /**
     * Handle the Column "updated" event.
     */
    public function updated(Column $column): void
    {
        broadcast(new ColumnUpdated($column, 'updated'));
    }

    /**
     * Handle the Column "deleted" event.
     */
    public function deleted(Column $column): void
    {
        broadcast(new ColumnUpdated($column, 'deleted'));
    }
}
