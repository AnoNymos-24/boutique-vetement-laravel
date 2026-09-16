<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Confirmation envoyée au client quand sa commande est annulée — que
 * l'annulation vienne d'elle-même (Shop\AccountController::cancelOrder())
 * ou de l'équipe (OrderController::cancel()).
 */
class OrderCancelledNotification extends Notification
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
        return (new MailMessage)
            ->subject("Commande {$this->order->order_number} annulée")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Votre commande {$this->order->order_number} a bien été annulée.")
            ->line("Total : {$this->order->totalFormatted()}")
            ->line("Si vous n'êtes pas à l'origine de cette annulation, ou pour toute question, n'hésitez pas à nous contacter.")
            ->action('Nous contacter', route('shop.contact'));
    }
}
