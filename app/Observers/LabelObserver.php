<?php

namespace App\Observers;

use App\Models\Label;
use App\Events\LabelUpdated;

class LabelObserver
{
    /**
     * Handle the Label "created" event.
     */
    public function created(Label $label): void
    {
        // Para labels, precisamos identificar o board através das tasks relacionadas
        $boardId = $this->getBoardIdFromLabel($label);
        if ($boardId) {
            broadcast(new LabelUpdated($label, 'created', $boardId));
        }
    }

    /**
     * Handle the Label "updated" event.
     */
    public function updated(Label $label): void
    {
        $boardId = $this->getBoardIdFromLabel($label);
        if ($boardId) {
            broadcast(new LabelUpdated($label, 'updated', $boardId));
        }
    }

    /**
     * Handle the Label "deleted" event.
     */
    public function deleted(Label $label): void
    {
        $boardId = $this->getBoardIdFromLabel($label);
        if ($boardId) {
            broadcast(new LabelUpdated($label, 'deleted', $boardId));
        }
    }

    /**
     * Handle the Label "attached" event (when attached to a task).
     */
    public function attached($label, $task): void
    {
        $boardId = $task->column->board_id ?? null;
        if ($boardId) {
            broadcast(new LabelUpdated($label, 'attached', $boardId, ['task_id' => $task->id]));
        }
    }

    /**
     * Handle the Label "detached" event (when detached from a task).
     */
    public function detached($label, $task): void
    {
        $boardId = $task->column->board_id ?? null;
        if ($boardId) {
            broadcast(new LabelUpdated($label, 'detached', $boardId, ['task_id' => $task->id]));
        }
    }

    /**
     * Get board ID from label through related tasks.
     */
    private function getBoardIdFromLabel(Label $label): ?int
    {
        // Busca um board através das tasks que usam esta label
        $task = $label->tasks()->with('column')->first();
        return $task?->column?->board_id;
    }
}
