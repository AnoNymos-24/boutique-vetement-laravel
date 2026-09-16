<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Envoyée au client quand son paiement (Orange Money/Wave) est vérifié ou
 * rejeté par l'équipe — la commande OrderConfirmationNotification initiale
 * annonce déjà ce délai de vérification (sous 24h), ceci en est la conclusion.
 * Sans objet pour le paiement à la livraison (rien à vérifier à distance).
 */
class OrderPaymentStatusNotification extends Notification
{
    public function __construct(public Order $order) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        if ($this->order->payment_status === 'rejete') {
            return (new MailMessage)
                ->subject("Paiement non confirmé — commande {$this->order->order_number}")
                ->greeting("Bonjour {$notifiable->name},")
                ->line("Nous n'avons pas pu vérifier le paiement de votre commande {$this->order->order_number}.")
                ->line("Merci de nous contacter afin de régulariser votre commande.")
                ->action('Nous contacter', route('shop.contact'));
        }

        return (new MailMessage)
            ->subject("Paiement confirmé — commande {$this->order->order_number}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Votre paiement pour la commande {$this->order->order_number} a bien été vérifié.")
            ->line('Votre commande est maintenant en cours de préparation.')
            ->action('Suivre ma commande', route('shop.orders.tracking', $this->order->order_number));
    }
}
