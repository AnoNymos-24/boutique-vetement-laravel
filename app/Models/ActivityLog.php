<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'action', 'description', 'subject_type', 'subject_id', 'ip_address', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Point d'entrée unique pour tracer une action back-office — appelé directement
     * depuis les contrôleurs aux endroits qui comptent (pas d'observer générique :
     * on trace des actions métier significatives, pas chaque UPDATE SQL).
     */
    public static function record(string $action, string $description, ?Model $subject = null): self
    {
        return static::create([
            // Explicitement le garde 'web' (équipe) et jamais auth()->id() sans
            // précision : le middleware Authenticate appelle Auth::shouldUse()
            // sur le garde qui a authentifié la requête, donc sur une route
            // cliente (garde 'customer') auth()->id() devient l'ID du client, pas
            // d'un membre du staff — et user_id a une clé étrangère vers `users`
            // (staff uniquement), ce qui provoquait une violation d'intégrité dès
            // qu'une action cliente (ex. annulation de commande) tentait de
            // journaliser ici. Un log sans membre du staff associé (action
            // cliente en self-service) reste correct : la vue admin affiche déjà
            // "—" pour un user_id nul.
            'user_id' => Auth::guard('web')->id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'ip_address' => app(Request::class)->ip(),
            'created_at' => now(),
        ]);
    }
}
