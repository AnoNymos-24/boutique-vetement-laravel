<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * La notification ResetPassword fournie par Laravel construit toujours son lien
 * avec route('password.reset', ...) — un nom de route unique, sans notion de
 * garde. Dans cette appli, ce nom appartient à l'espace équipe (/admin/reset-
 * password, garde web) : une cliente qui cliquait sur le lien reçu par e-mail
 * atterrissait donc sur le formulaire de réinitialisation de l'ÉQUIPE, où son
 * compte "customer" n'existe pas — d'où "Aucun compte ne correspond à cette
 * adresse e-mail." au moment de valider son nouveau mot de passe. Cette classe
 * pointe explicitement vers la route customer.password.reset.
 */
class CustomerResetPasswordNotification extends Notification
{
    public function __construct(public string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('customer.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe')
            ->greeting("Bonjour {$notifiable->name},")
            ->line('Vous recevez cet e-mail car une demande de réinitialisation de mot de passe a été effectuée pour votre compte.')
            ->action('Réinitialiser mon mot de passe', $url)
            ->line('Ce lien expirera dans ' . config('auth.passwords.customers.expire') . ' minutes.')
            ->line("Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet e-mail sans rien changer.");
    }
}
