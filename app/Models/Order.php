<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'order_number', 'channel', 'customer_id', 'customer_name', 'customer_phone', 'customer_address',
        'status', 'payment_status', 'payment_method', 'payment_reference',
        'total', 'promo_code', 'discount_amount', 'note',
        'confirmed_at', 'prepared_at', 'shipped_at', 'delivered_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'discount_amount' => 'integer',
            'confirmed_at' => 'datetime',
            'prepared_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /** Ordre du cycle de vie normal (hors annulation, accessible depuis n'importe quel statut non terminal). */
    public const FLOW = ['en_attente', 'confirmee', 'preparee', 'expediee', 'livree'];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public static function generateOrderNumber(): string
    {
        do {
            $number = 'CMD-' . strtoupper(Str::random(6));
        } while (self::where('order_number', $number)->exists());

        return $number;
    }

    public function totalFormatted(): string
    {
        return number_format($this->total / 100, 2, ',', ' ') . ' €';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'en_attente' => 'En attente',
            'confirmee' => 'Confirmée',
            'preparee' => 'Préparée',
            'expediee' => 'Expédiée',
            'livree' => 'Livrée',
            'annulee' => 'Annulée',
        };
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            'en_attente_verification' => 'À vérifier',
            'verifie' => 'Payé',
            'rejete' => 'Rejeté',
        };
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            'orange_money' => 'Orange Money',
            'wave' => 'Wave',
            'especes' => 'Espèces',
            default => '—',
        };
    }

    public function isManualSale(): bool
    {
        return $this->channel === 'vente_manuelle';
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, ['livree', 'annulee'], true);
    }

    public function nextStatus(): ?string
    {
        $index = array_search($this->status, self::FLOW, true);

        if ($index === false || $index === count(self::FLOW) - 1) {
            return null;
        }

        return self::FLOW[$index + 1];
    }

    public function nextStatusLabel(): ?string
    {
        return match ($this->nextStatus()) {
            'confirmee' => 'Confirmer la commande',
            'preparee' => 'Marquer comme préparée',
            'expediee' => 'Marquer comme expédiée',
            'livree' => 'Marquer comme livrée',
            default => null,
        };
    }
}
