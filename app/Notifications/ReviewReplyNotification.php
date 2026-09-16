<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Réponse envoyée par e-mail à l'auteur d'un avis — la réponse est aussi
 * affichée publiquement à côté de l'avis (fiche produit + "Mes avis" du
 * compte client), cet e-mail est le signal actif qui lui évite de devoir
 * revenir consulter le site pour le savoir.
 */
class ReviewReplyNotification extends Notification
{
    public function __construct(public Review $review) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Réponse à votre avis sur {$this->review->product->name}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Vous avez laissé un avis sur « {$this->review->product->name} » :")
            ->line("« {$this->review->comment} »")
            ->line('Notre réponse :')
            ->line($this->review->admin_reply);

        if ($this->review->product) {
            $mail->action('Voir le produit', route('shop.product', $this->review->product));
        }

        return $mail;
    }
}
