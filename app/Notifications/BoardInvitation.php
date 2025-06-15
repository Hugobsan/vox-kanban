<?php

namespace App\Notifications;

use App\Models\BoardUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class BoardInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    public $boardUser;

    /**
     * Create a new notification instance.
     */
    public function __construct(BoardUser $boardUser)
    {
        $this->boardUser = $boardUser;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $roleName = $this->getRoleName($this->boardUser->role_in_board);
        
        return (new MailMessage)
            ->subject('Você foi convidado para um quadro Kanban')
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line('Você foi convidado para participar do quadro "' . $this->boardUser->board->name . '" como ' . $roleName . '.')
            ->line('Clique no botão abaixo para acessar o quadro:')
            ->action('Acessar Quadro', url('/boards/' . $this->boardUser->board->id))
            ->line('Obrigado por usar o Vox Kanban!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'board_id' => $this->boardUser->board->id,
            'board_name' => $this->boardUser->board->name,
            'role' => $this->boardUser->role_in_board,
            'invited_by' => Auth::check() ? Auth::user()->name : 'Sistema',
        ];
    }

    /**
     * Get human readable role name
     */
    private function getRoleName(string $role): string
    {
        return match($role) {
            'owner' => 'proprietário',
            'editor' => 'editor',
            'viewer' => 'visualizador',
            default => $role
        };
    }
}
