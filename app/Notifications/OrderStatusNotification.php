<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Envoyée au client à chaque étape du cycle de vie de sa commande (confirmée,
 * préparée, expédiée, livrée) — distincte de AdminAlertNotification (équipe) et
 * de OrderPaymentStatusNotification (statut du paiement, pas de la commande).
 */
class OrderStatusNotification extends Notification
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
        [$subject, $intro] = match ($this->order->status) {
            'confirmee' => ["Commande {$this->order->order_number} confirmée", 'Votre commande a été confirmée et est en cours de préparation.'],
            'preparee' => ["Commande {$this->order->order_number} préparée", 'Votre commande est prête et sera bientôt expédiée.'],
            'expediee' => ["Commande {$this->order->order_number} expédiée", 'Votre commande est en route vers vous !'],
            'livree' => ["Commande {$this->order->order_number} livrée", 'Votre commande a été livrée. Merci pour votre achat !'],
            default => ["Commande {$this->order->order_number} mise à jour", "Statut de votre commande : {$this->order->statusLabel()}."],
        };

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting("Bonjour {$notifiable->name},")
            ->line($intro)
            ->action('Suivre ma commande', route('shop.orders.tracking', $this->order->order_number));

        if ($this->order->status === 'livree') {
            $mail->line("N'hésitez pas à laisser un avis sur les articles reçus depuis votre espace client.");
        }

        return $mail;
    }
}
