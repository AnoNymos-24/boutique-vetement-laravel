<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Confirmation envoyée au client lui-même après une commande en ligne — distincte
 * de AdminAlertNotification, qui prévient l'équipe. Les deux partent du même
 * événement (Shop\CheckoutController::store()) mais vers des destinataires différents.
 */
class OrderConfirmationNotification extends Notification
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
        $mail = (new MailMessage)
            ->subject("Confirmation de votre commande {$this->order->order_number}")
            ->greeting("Merci pour votre commande, {$notifiable->name} !")
            ->line("Votre commande {$this->order->order_number} a bien été enregistrée et est en attente de vérification du paiement.");

        foreach ($this->order->items as $item) {
            $label = $item->variant_label ? "{$item->product_name} ({$item->variant_label})" : $item->product_name;
            $mail->line("• {$label} × {$item->quantity} — {$item->subtotalFormatted()}");
        }

        $mail->line("Total : {$this->order->totalFormatted()}");

        $mail->line($this->order->payment_method === 'especes'
            ? 'Vous serez appelé(e) prochainement pour confirmer votre commande.'
            : 'Vous recevrez un e-mail de confirmation dès que votre paiement sera vérifié, sous 24h.');

        return $mail
            ->action('Suivre ma commande', route('shop.orders.tracking', $this->order->order_number))
            ->line('Nous vous tiendrons informé(e) par e-mail à chaque étape, jusqu\'à la livraison.');
    }
}
