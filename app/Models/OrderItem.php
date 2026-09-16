<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'product_id', 'product_variant_id', 'product_name', 'variant_label',
        'unit_price', 'unit_cost', 'quantity', 'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'integer',
            'unit_cost' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function unitPriceFormatted(): string
    {
        return StoreSetting::current()->formatMoney($this->unit_price);
    }

    public function subtotalFormatted(): string
    {
        return StoreSetting::current()->formatMoney($this->subtotal);
    }

    public function margin(): int
    {
        return $this->subtotal - ($this->unit_cost * $this->quantity);
    }
}
