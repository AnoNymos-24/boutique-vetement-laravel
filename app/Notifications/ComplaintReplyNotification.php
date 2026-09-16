<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Réponse envoyée par e-mail à l'auteur d'une réclamation — jusqu'ici la réponse
 * n'était visible que dans son espace compte, sans notification active ; un
 * e-mail garantit qu'elle en soit informée même sans revenir consulter le site.
 */
class ComplaintReplyNotification extends Notification
{
    public function __construct(public Complaint $complaint) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Réponse à votre réclamation — {$this->complaint->subject}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Vous nous avez signalé :")
            ->line("« {$this->complaint->message} »")
            ->line('Notre réponse :')
            ->line($this->complaint->admin_reply)
            ->action('Voir mes réclamations', route('customer.complaints'));
    }
}
