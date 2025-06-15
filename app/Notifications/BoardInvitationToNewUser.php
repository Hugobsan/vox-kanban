<?php

namespace App\Notifications;

use App\Models\Board;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BoardInvitationToNewUser extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Board $board,
        public string $role,
        public string $email
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $roleNames = [
            'owner' => 'proprietário',
            'editor' => 'editor',
            'viewer' => 'visualizador'
        ];

        $roleName = $roleNames[$this->role] ?? $this->role;

        return (new MailMessage)
            ->subject('Convite para participar do quadro: ' . $this->board->name)
            ->greeting('Olá!')
            ->line('Você foi convidado para participar do quadro **' . $this->board->name . '** como **' . $roleName . '**.')
            ->line('Para aceitar este convite, você precisa criar uma conta no Vox Kanban.')
            ->action('Criar Conta e Aceitar Convite', url('/register?email=' . urlencode($this->email) . '&board=' . $this->board->id))
            ->line('Se você já possui uma conta, faça login e entre em contato com o administrador do quadro.')
            ->line('Obrigado por usar o Vox Kanban!')
            ->salutation('Atenciosamente, Equipe Vox Kanban');
    }
}
