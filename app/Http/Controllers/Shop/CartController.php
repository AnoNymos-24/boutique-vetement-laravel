<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Services\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Cart $cart): View
    {
        return view('shop.cart', [
            'items' => $cart->items(),
            'subtotal' => $cart->subtotal(),
        ]);
    }

    public function store(Request $request, Cart $cart): RedirectResponse
    {
        $data = $request->validate([
            'product_variant_id' => ['required', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $variant = ProductVariant::findOrFail($data['product_variant_id']);

        if ($variant->stock_quantity <= 0) {
            return back()->with('error', 'Cette variante est en rupture de stock.');
        }

        $cart->add($data['product_variant_id'], $data['quantity']);

        return back()->with('status', 'Article ajouté au panier.');
    }

    public function update(Request $request, Cart $cart, ProductVariant $variant): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $cart->updateQuantity($variant->id, $data['quantity']);

        return back();
    }

    public function destroy(Cart $cart, ProductVariant $variant): RedirectResponse
    {
        $cart->remove($variant->id);

        return back()->with('status', 'Article retiré du panier.');
    }
}
