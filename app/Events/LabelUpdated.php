<?php

namespace App\Events;

use App\Models\Label;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LabelUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $label;
    public $action;
    public $data;
    public $boardId;

    /**
     * Create a new event instance.
     */
    public function __construct(Label $label, string $action, int $boardId, array $data = [])
    {
        $this->label = $label;
        $this->action = $action;
        $this->boardId = $boardId;
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("board.{$this->boardId}"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'LabelUpdated';
    }

    /**
     * Data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'label' => $this->label,
            'board_id' => $this->boardId,
            'data' => $this->data,
            'timestamp' => now()->toISOString(),
        ];
    }
}
