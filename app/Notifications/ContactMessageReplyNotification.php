<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Réponse envoyée à l'auteur d'un message de contact — celui-ci n'a pas de compte
 * (formulaire public, sans connexion) donc l'e-mail est le SEUL moyen pour lui de
 * voir la réponse de la boutique, contrairement aux réclamations (visibles dans
 * l'espace compte du client). Envoyée via une route à la demande (pas de modèle
 * Notifiable côté destinataire), voir ContactMessageController::reply().
 */
class ContactMessageReplyNotification extends Notification
{
    public function __construct(public ContactMessage $contactMessage) {}

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
            ->subject("Re : {$this->contactMessage->subject}")
            ->greeting("Bonjour {$this->contactMessage->name},")
            ->line("Vous nous avez écrit :")
            ->line("« {$this->contactMessage->body} »")
            ->line('Notre réponse :')
            ->line($this->contactMessage->admin_reply)
            ->line("Pour toute autre question, répondez simplement à cet e-mail ou utilisez à nouveau notre formulaire de contact.");
    }
}
