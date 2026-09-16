<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Alerte générique envoyée à toute l'équipe (admin + vendeurs) pour tout sujet
 * qui nécessite leur attention : nouvelle commande, réclamation, message, ou
 * action importante. Volontairement sans détails sensibles dans l'e-mail —
 * juste de quoi identifier le sujet, avec un lien pour se connecter et traiter.
 */
class AdminAlertNotification extends Notification
{
    public function __construct(
        public string $category,
        public string $subjectLine,
        public string $summary,
        public string $url,
    ) {}

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
            ->subject("[Boutique] {$this->category} — {$this->subjectLine}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line($this->summary)
            ->action('Se connecter à la plateforme', $this->url)
            ->line("Merci de vous connecter pour traiter ce sujet dès que possible.");
    }

    /**
     * Envoie l'alerte à tous les membres actifs de l'équipe (admin + vendeurs) —
     * l'admin est toujours inclus puisqu'il fait partie de l'équipe active, donc
     * reçoit systématiquement tout, en plus des vendeurs concernés.
     *
     * Envoyée une personne à la fois (pas Notification::send() sur toute la
     * collection) et chaque envoi est protégé individuellement : un échec SMTP
     * sur un destinataire (réseau, quota Gmail...) ne doit jamais empêcher les
     * autres de recevoir l'alerte, ni faire planter l'action métier qui l'a
     * déclenchée (commande, avis, réclamation...).
     */
    public static function broadcastToTeam(string $category, string $subjectLine, string $summary, string $url): void
    {
        $notification = new self($category, $subjectLine, $summary, $url);

        foreach (User::where('is_active', true)->get() as $member) {
            try {
                $member->notify($notification);
            } catch (\Throwable $e) {
                // Voir note ci-dessus — on continue avec le membre suivant, mais on
                // garde une trace : un échec silencieux et invisible serait pire.
                Log::warning("Échec d'envoi d'alerte à {$member->email} : {$e->getMessage()}");
            }
        }
    }
}
