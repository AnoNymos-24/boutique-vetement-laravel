<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Complaint extends Model
{
    protected $fillable = [
        'customer_id', 'order_id', 'subject', 'message', 'status', 'admin_reply', 'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'replied_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function refund(): HasOne
    {
        return $this->hasOne(Refund::class);
    }

    public function hasReply(): bool
    {
        return ! is_null($this->admin_reply);
    }

    public function hasRefund(): bool
    {
        return $this->refund()->exists();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'nouveau' => 'Nouvelle',
            'en_cours' => 'En cours',
            'resolu' => 'Résolue',
            'ferme' => 'Fermée',
        };
    }
}
