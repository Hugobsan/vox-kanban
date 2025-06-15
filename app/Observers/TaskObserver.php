<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\Board;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        // Gera a referência de forma assíncrona após a criação da task
        $this->generateTaskReference($task);
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
