<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'size', 'color', 'sku', 'stock_quantity', 'low_stock_threshold'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest();
    }

    public function stockState(): string
    {
        if ($this->stock_quantity <= 0) {
            return 'rupture';
        }

        if ($this->stock_quantity <= $this->low_stock_threshold) {
            return 'bas';
        }

        return 'ok';
    }

    /** "M / Rouge", "Taille unique", "Rouge"… selon ce qui est renseigné. */
    public function label(): string
    {
        $parts = array_filter([$this->size, $this->color]);

        return $parts ? implode(' / ', $parts) : 'Taille unique';
    }
}
