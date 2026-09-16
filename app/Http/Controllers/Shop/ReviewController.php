<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Review;
use App\Notifications\AdminAlertNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(): View
    {
        $customer = Auth::guard('customer')->user();

        $reviewedProductIds = Review::where('customer_id', $customer->id)->pluck('product_id');

        $eligibleItems = OrderItem::with('product.images')
            ->whereHas('order', fn ($q) => $q->where('customer_id', $customer->id)->where('payment_status', 'verifie'))
            ->whereNotIn('product_id', $reviewedProductIds)
            ->get()
            ->unique('product_id');

        $myReviews = Review::with('product')
            ->where('customer_id', $customer->id)
            ->latest()
            ->get();

        return view('shop.account.reviews', [
            'customer' => $customer,
            'eligibleItems' => $eligibleItems,
            'myReviews' => $myReviews,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:2000'],
        ]);

        $orderItem = OrderItem::whereHas('order', fn ($q) => $q
                ->where('customer_id', $customer->id)
                ->where('payment_status', 'verifie'))
            ->where('product_id', $data['product_id'])
            ->first();

        if (! $orderItem) {
            return back()->with('error', "Vous ne pouvez commenter que les articles que vous avez déjà achetés.")->with('review_feedback', true);
        }

        if (Review::where('customer_id', $customer->id)->where('product_id', $data['product_id'])->exists()) {
            return back()->with('error', 'Vous avez déjà laissé un avis pour ce produit.')->with('review_feedback', true);
        }

        // Publication immédiate, sans modération — l'admin garde la main pour
        // répondre ou supprimer un avis a posteriori (ReviewController back-office),
        // mais rien ne bloque plus sa visibilité publique dès l'envoi.
        $review = Review::create([
            'product_id' => $data['product_id'],
            'customer_id' => $customer->id,
            'order_id' => $orderItem->order_id,
            'author_name' => $customer->name,
            'rating' => $data['rating'],
            'comment' => $data['comment'],
            'status' => 'publie',
        ]);

        AdminAlertNotification::broadcastToTeam(
            'Nouveau commentaire',
            "{$review->rating}/5 — {$orderItem->product_name}",
            "{$customer->name} a laissé un avis ({$review->rating}/5) sur « {$orderItem->product_name} », publié automatiquement.",
            route('reviews')
        );

        return back()->with('status', 'Merci ! Votre avis est publié.')->with('review_feedback', true);
    }
}
