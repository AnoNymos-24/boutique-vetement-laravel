<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Product $product): View
    {
        abort_unless($product->status === 'active', 404);

        $product->load(['category', 'images', 'variants', 'labels', 'tags']);

        $reviews = $product->reviews()
            ->where('status', 'publie')
            ->latest()
            ->get();

        $relatedProducts = Product::with('images')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->inRandomOrder()
            ->limit(4)
            ->get();

        $customer = Auth::guard('customer')->user();

        // État du droit à commenter, calculé une fois ici plutôt que dans la vue :
        // invité → lien de connexion ; connecté sans achat vérifié → message bloquant ;
        // déjà commenté → rien à afficher ; sinon → formulaire.
        $canReview = false;
        $alreadyReviewed = false;

        if ($customer) {
            $alreadyReviewed = Review::where('customer_id', $customer->id)
                ->where('product_id', $product->id)
                ->exists();

            $canReview = ! $alreadyReviewed && OrderItem::whereHas('order', fn ($q) => $q
                    ->where('customer_id', $customer->id)
                    ->where('payment_status', 'verifie'))
                ->where('product_id', $product->id)
                ->exists();
        }

        return view('shop.product', [
            'product' => $product,
            'reviews' => $reviews,
            'relatedProducts' => $relatedProducts,
            'customer' => $customer,
            'canReview' => $canReview,
            'alreadyReviewed' => $alreadyReviewed,
        ]);
    }
}
