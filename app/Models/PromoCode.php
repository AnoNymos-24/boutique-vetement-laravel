<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    /** @use HasFactory<\Database\Factories\PromoCodeFactory> */
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'value', 'min_order_amount', 'max_uses',
        'used_count', 'starts_at', 'expires_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'expires_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isExhausted(): bool
    {
        return $this->max_uses !== null && $this->used_count >= $this->max_uses;
    }

    /** Statut du code lui-même, indépendant du panier (utilisé pour l'aperçu avant d'avoir un sous-total). */
    public function isActiveAndAvailable(): bool
    {
        if (! $this->is_active || $this->isExpired() || $this->isExhausted()) {
            return false;
        }

        if ($this->starts_at !== null && $this->starts_at->isFuture()) {
            return false;
        }

        return true;
    }

    public function meetsMinimum(int $orderSubtotal): bool
    {
        return $this->min_order_amount === null || $orderSubtotal >= $this->min_order_amount;
    }

    /** Validation complète (statut + panier) — c'est la seule à utiliser pour valider une commande. */
    public function isUsable(int $orderSubtotal = 0): bool
    {
        return $this->isActiveAndAvailable() && $this->meetsMinimum($orderSubtotal);
    }

    public function statusLabel(): string
    {
        if (! $this->is_active) {
            return 'Inactif';
        }

        if ($this->isExpired()) {
            return 'Expiré';
        }

        if ($this->isExhausted()) {
            return 'Épuisé';
        }

        return 'Actif';
    }

    public function discountFor(int $subtotal): int
    {
        $discount = $this->type === 'pourcentage'
            ? (int) round($subtotal * $this->value / 100)
            : $this->value;

        return min($discount, $subtotal);
    }

    public function valueLabel(): string
    {
        return $this->type === 'pourcentage'
            ? "{$this->value}%"
            : number_format($this->value / 100, 2, ',', ' ') . ' €';
    }
}
