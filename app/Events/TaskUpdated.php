<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $action;
    public $data;

    /**
     * Create a new event instance.
     */
    public function __construct(Task $task, string $action, array $data = [])
    {
        $this->task = $task;
        $this->action = $action;
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Busca o board_id através da coluna
        $boardId = $this->task->column->board_id ?? $this->data['board_id'] ?? null;
        
        return [
            new Channel("board.{$boardId}"), // Temporariamente público para teste
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'TaskUpdated';
    }

    /**
     * Data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'task' => $this->task->load(['labels', 'column']),
            'data' => $this->data,
            'timestamp' => now()->toISOString(),
        ];
    }
}
