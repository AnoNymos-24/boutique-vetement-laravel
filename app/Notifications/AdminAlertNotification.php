<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

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
     * Envoie l'alerte à tous les membres actifs de l'équipe (admin + vendeurs).
     */
    public static function broadcastToTeam(string $category, string $subjectLine, string $summary, string $url): void
    {
        $team = User::where('is_active', true)->get();

        if ($team->isNotEmpty()) {
            NotificationFacade::send($team, new self($category, $subjectLine, $summary, $url));
        }
    }
}
