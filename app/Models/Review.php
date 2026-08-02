<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    /** @use HasFactory<\Database\Factories\ReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id', 'customer_id', 'order_id', 'author_name',
        'rating', 'comment', 'status', 'admin_reply', 'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'replied_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isVerifiedPurchase(): bool
    {
        return $this->order_id !== null;
    }

    public function hasReply(): bool
    {
        return $this->admin_reply !== null;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'en_attente' => 'À modérer',
            'publie' => 'Publié',
            'rejete' => 'Rejeté',
        };
    }
}
