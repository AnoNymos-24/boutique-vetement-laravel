<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $fillable = [
        'order_id', 'complaint_id', 'processed_by', 'reason', 'amount', 'method', 'note', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Motifs alignés sur l'article 9.1 des CGU — voir la migration pour le
     * même commentaire côté colonne. "annulation_avant_expedition" et
     * "indisponibilite" ne sont pas proposés depuis le formulaire Réclamations
     * (ils ne naissent pas d'une réclamation) mais restent dans l'énumération
     * pour une éventuelle réutilisation depuis l'annulation de commande.
     */
    public static function reasons(): array
    {
        return [
            'annulation_avant_expedition' => 'Commande annulée avant expédition',
            'produit_defectueux' => 'Produit défectueux',
            'non_conforme' => 'Non conforme à la description',
            'erreur_commande' => 'Erreur de commande de notre part',
            'indisponibilite' => 'Indisponibilité du produit après paiement',
            'retour_reclamation' => 'Retour accepté (réclamation)',
        ];
    }

    public function reasonLabel(): string
    {
        return self::reasons()[$this->reason] ?? $this->reason;
    }

    public function amountFormatted(): string
    {
        return StoreSetting::current()->formatMoney($this->amount);
    }
}
