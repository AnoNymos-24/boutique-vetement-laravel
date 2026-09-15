<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    protected $fillable = [
        'customer_id', 'name', 'email', 'phone', 'subject', 'body', 'status', 'admin_reply', 'replied_at',
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

    public function hasReply(): bool
    {
        return ! is_null($this->admin_reply);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'nouveau' => 'Nouveau',
            'lu' => 'Lu',
            'traite' => 'Traité',
        };
    }
}
