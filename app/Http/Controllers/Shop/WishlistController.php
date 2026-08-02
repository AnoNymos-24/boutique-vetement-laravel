<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(): View
    {
        $customer = Auth::guard('customer')->user();

        $products = Product::with(['category', 'images', 'variants'])
            ->whereHas('wishlists', fn ($q) => $q->where('customer_id', $customer->id))
            ->get();

        return view('shop.account.wishlist', ['products' => $products]);
    }

    public function toggle(Product $product): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        $wishlist = $customer->wishlists()->where('product_id', $product->id)->first();

        if ($wishlist) {
            $wishlist->delete();
            $message = 'Retiré de vos favoris.';
        } else {
            $customer->wishlists()->create(['product_id' => $product->id]);
            $message = 'Ajouté à vos favoris.';
        }

        return back()->with('status', $message);
    }
}
