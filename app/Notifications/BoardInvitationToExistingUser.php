<?php

namespace App\Notifications;

use App\Models\Board;
use App\Models\BoardUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BoardInvitationToExistingUser extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public BoardUser $boardUser
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $roleNames = [
            'owner' => 'proprietário',
            'editor' => 'editor', 
            'viewer' => 'visualizador'
        ];

        $roleName = $roleNames[$this->boardUser->role_in_board->value] ?? $this->boardUser->role_in_board->value;

        return (new MailMessage)
            ->subject('Você foi adicionado ao quadro: ' . $this->boardUser->board->name)
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line('Você foi adicionado ao quadro **' . $this->boardUser->board->name . '** como **' . $roleName . '**.')
            ->line('Agora você pode acessar e colaborar neste quadro.')
            ->action('Acessar Quadro', url('/boards/' . $this->boardUser->board->id))
            ->line('Obrigado por usar o Vox Kanban!')
            ->salutation('Atenciosamente, Equipe Vox Kanban');
    }

    public function toArray($notifiable): array
    {
        return [
            'board_id' => $this->boardUser->board_id,
            'board_name' => $this->boardUser->board->name,
            'role' => $this->boardUser->role_in_board->value,
            'message' => 'Você foi adicionado ao quadro: ' . $this->boardUser->board->name
        ];
    }
}
