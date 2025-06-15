<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
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
        return (new MailMessage)
                    ->subject('Bem-vindo ao Vox Kanban! 🎉')
                    ->greeting('Olá, ' . $notifiable->name . '!')
                    ->line('Obrigado por criar sua conta no Vox Kanban! Estamos muito felizes em tê-lo como parte da nossa comunidade.')
                    ->line('O Vox Kanban é uma ferramenta poderosa de gerenciamento de projetos que vai ajudar você e sua equipe a organizarem melhor o trabalho e aumentarem a produtividade.')
                    ->action('Acessar Vox Kanban', config('app.url'))
                    ->line('Se você tiver alguma dúvida, não hesite em entrar em contato conosco. Estamos aqui para ajudar!')
                    ->salutation('Obrigado, Equipe ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
