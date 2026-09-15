<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;

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
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'ip_address' => app(Request::class)->ip(),
            'created_at' => now(),
        ]);
    }
}
