<?php

namespace App\Notifications;

use App\Models\Board;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\AnonymousNotifiable;

class WelcomeNewUserToBoardNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $board;
    public $role;
    public $email;

    /**
     * Create a new notification instance.
     */
    public function __construct(Board $board, string $role, string $email)
    {
        $this->board = $board;
        $this->role = $role;
        $this->email = $email;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $roleName = $this->getRoleName($this->role);
        
        return (new MailMessage)
            ->subject('Você foi convidado para um quadro Kanban - Crie sua conta')
            ->greeting('Olá!')
            ->line('Você foi convidado para participar do quadro "' . $this->board->name . '" como ' . $roleName . ' no Vox Kanban.')
            ->line('Para acessar o quadro, você precisa criar uma conta usando este e-mail.')
            ->line('Clique no botão abaixo para se registrar:')
            ->action('Criar Conta', url('/register?email=' . urlencode($this->email)))
            ->line('Após criar sua conta, você terá acesso automático ao quadro.')
            ->line('Bem-vindo ao Vox Kanban!');
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
