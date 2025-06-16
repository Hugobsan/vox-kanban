<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\Board;
use App\Events\TaskCreated;
use App\Events\TaskUpdated;
use App\Events\TaskDeleted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        Log::info('TaskObserver: Task created', ['task_id' => $task->id]);
        
        // Gera a referência de forma assíncrona após a criação da task
        $this->generateTaskReference($task);
        
        // Broadcast the task creation
        broadcast(new TaskCreated($task));
        Log::info('TaskObserver: TaskCreated event broadcasted', ['task_id' => $task->id]);
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        Log::info('TaskObserver: Task updated', ['task_id' => $task->id, 'changes' => $task->getChanges()]);
        
        // Verificar se o board_id pode ser encontrado
        $boardId = $task->column->board_id ?? null;
        Log::info('TaskObserver: Board ID found', [
            'task_id' => $task->id,
            'board_id' => $boardId,
            'column_id' => $task->column_id
        ]);
        
        // Criar o evento
        $event = new TaskUpdated($task, 'updated');
        Log::info('TaskObserver: Created TaskUpdated event', [
            'task_id' => $task->id,
            'board_id' => $boardId,
            'event_class' => get_class($event)
        ]);
        
        // Broadcast o evento
        $result = broadcast($event);
        Log::info('TaskObserver: TaskUpdated event broadcasted', [
            'task_id' => $task->id,
            'board_id' => $boardId,
            'broadcast_result' => $result
        ]);
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        Log::info('TaskObserver: Task deleted', ['task_id' => $task->id]);
        
        // Captura o board_id antes da exclusão
        $boardId = $task->column->board_id ?? null;
        if ($boardId) {
            broadcast(new TaskDeleted($task, $boardId));
            Log::info('TaskObserver: TaskDeleted event broadcasted', ['task_id' => $task->id, 'board_id' => $boardId]);
        }
    }

    /**
     * Gera a referência da task de forma assíncrona
     */
    private function generateTaskReference(Task $task): void
    {
        try {
            DB::transaction(function () use ($task) {
                // Busca o board através da coluna com lock para evitar condições de corrida
                $board = Board::whereHas('columns', function ($query) use ($task) {
                    $query->where('id', $task->column_id);
                })->lockForUpdate()->first();
                
                if (!$board) {
                    throw new \Exception("Board não encontrado para a task {$task->id}");
                }
                
                // Obtém o próximo número da task e monta a referência
                $taskNumber = $board->next_task_number;
                $reference = $board->key . '-' . $taskNumber;
                
                // Atualiza a task com a referência
                $task->update(['reference' => $reference]);
                
                // Incrementa o contador do board
                $board->increment('next_task_number');
            });
        } catch (\Exception $e) {
            Log::error('Erro ao gerar referência da task', [
                'task_id' => $task->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
