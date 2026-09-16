<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name', 'category_id', 'description', 'price', 'cost_price', 'compare_at_price', 'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'cost_price' => 'integer',
            'compare_at_price' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class, 'product_label');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function stockMovements(): HasManyThrough
    {
        return $this->hasManyThrough(StockMovement::class, ProductVariant::class)->latest('stock_movements.created_at');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function totalStock(): int
    {
        return $this->variants->sum('stock_quantity');
    }

    /** État agrégé sur toutes les variantes — nécessite `variants` chargée (eager load) pour éviter le N+1. */
    public function stockState(): string
    {
        $states = $this->variants->map->stockState();

        if ($states->isEmpty() || $states->every(fn ($s) => $s === 'rupture')) {
            return 'rupture';
        }

        if ($states->contains('rupture') || $states->contains('bas')) {
            return 'bas';
        }

        return 'ok';
    }

    public function priceFormatted(): string
    {
        return StoreSetting::current()->formatMoney($this->price);
    }

    public function marginPerUnit(): ?int
    {
        return $this->cost_price === null ? null : $this->price - $this->cost_price;
    }
}
