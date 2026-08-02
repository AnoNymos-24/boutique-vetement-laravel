<?php

namespace App\Services;

use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * Panier en session — pas besoin de compte pour acheter.
 * On ne stocke que variant_id => quantité ; le prix est toujours relu en base
 * au moment de l'affichage/du paiement pour ne jamais figer un prix périmé.
 */
class Cart
{
    private const SESSION_KEY = 'cart';

    public function raw(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public function add(int $variantId, int $quantity = 1): void
    {
        $variant = ProductVariant::findOrFail($variantId);
        $cart = $this->raw();
        $current = $cart[$variantId] ?? 0;
        $cart[$variantId] = min($current + $quantity, $variant->stock_quantity);

        Session::put(self::SESSION_KEY, $cart);
    }

    public function updateQuantity(int $variantId, int $quantity): void
    {
        $cart = $this->raw();

        if ($quantity <= 0) {
            unset($cart[$variantId]);
        } else {
            $variant = ProductVariant::find($variantId);
            $cart[$variantId] = $variant ? min($quantity, $variant->stock_quantity) : $quantity;
        }

        Session::put(self::SESSION_KEY, $cart);
    }

    public function remove(int $variantId): void
    {
        $cart = $this->raw();
        unset($cart[$variantId]);
        Session::put(self::SESSION_KEY, $cart);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * Lignes du panier hydratées depuis la base, avec produit/variante à jour.
     * Une variante supprimée ou dont le stock est tombé à zéro est retirée silencieusement.
     */
    public function items(): Collection
    {
        $cart = $this->raw();

        if (empty($cart)) {
            return collect();
        }

        $variants = ProductVariant::with(['product.images'])
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        $items = collect();
        $changed = false;

        foreach ($cart as $variantId => $quantity) {
            $variant = $variants->get($variantId);

            if (! $variant || $variant->stock_quantity <= 0) {
                unset($cart[$variantId]);
                $changed = true;
                continue;
            }

            $clampedQuantity = min($quantity, $variant->stock_quantity);
            if ($clampedQuantity !== $quantity) {
                $cart[$variantId] = $clampedQuantity;
                $changed = true;
            }

            $items->push([
                'variant' => $variant,
                'product' => $variant->product,
                'quantity' => $clampedQuantity,
                'unit_price' => $variant->product->price,
                'subtotal' => $variant->product->price * $clampedQuantity,
            ]);
        }

        if ($changed) {
            Session::put(self::SESSION_KEY, $cart);
        }

        return $items;
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    public function subtotal(): int
    {
        return (int) $this->items()->sum('subtotal');
    }

    public function isEmpty(): bool
    {
        return empty($this->raw());
    }
}
