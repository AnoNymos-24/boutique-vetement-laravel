<?php

namespace App\Notifications;

use App\Models\Refund;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Confirmation envoyée au client quand un remboursement lié à sa réclamation
 * est enregistré — le remboursement lui-même (virement Orange Money/Wave/
 * espèces) est fait manuellement par l'équipe hors de l'app ; cette
 * notification confirme juste qu'il a eu lieu, avec le motif et le montant.
 */
class ComplaintRefundNotification extends Notification
{
    public function __construct(public Refund $refund) {}

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
            ->subject("Remboursement effectué — commande {$this->refund->order->order_number}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Suite à votre réclamation, nous avons procédé au remboursement de votre commande {$this->refund->order->order_number}.")
            ->line("Motif : {$this->refund->reasonLabel()}")
            ->line("Montant remboursé : {$this->refund->amountFormatted()}")
            ->line("Merci pour votre patience, et n'hésitez pas à nous recontacter pour toute question.");
    }
}
