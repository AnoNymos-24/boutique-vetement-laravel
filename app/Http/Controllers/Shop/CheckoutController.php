<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Notifications\AdminAlertNotification;
use App\Services\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(Cart $cart): View|RedirectResponse
    {
        if ($cart->isEmpty()) {
            return redirect()->route('shop.cart')->with('error', 'Votre panier est vide.');
        }

        $customer = Auth::guard('customer')->user();

        return view('shop.checkout', [
            'items' => $cart->items(),
            'subtotal' => $cart->subtotal(),
            'customer' => $customer,
            'addresses' => $customer->addresses()->orderByDesc('is_default')->get(),
        ]);
    }

    public function store(Request $request, Cart $cart): RedirectResponse
    {
        if ($cart->isEmpty()) {
            return redirect()->route('shop.cart')->with('error', 'Votre panier est vide.');
        }

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_address' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'in:orange_money,wave'],
            'payment_reference' => ['required', 'digits:6'],
        ]);

        $items = $cart->items();

        foreach ($items as $item) {
            $variant = ProductVariant::find($item['variant']->id);
            if (! $variant || $variant->stock_quantity < $item['quantity']) {
                return back()->withInput()->with('error',
                    "Stock insuffisant pour « {$item['product']->name} » : il n'en reste que " . ($variant->stock_quantity ?? 0) . '.'
                );
            }
        }

        $subtotal = $cart->subtotal();
        $customer = Auth::guard('customer')->user();

        $order = DB::transaction(function () use ($data, $items, $subtotal, $customer) {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'channel' => 'boutique_en_ligne',
                'customer_id' => $customer->id,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_address' => $data['customer_address'],
                'status' => 'en_attente',
                'payment_status' => 'en_attente_verification',
                'payment_method' => $data['payment_method'],
                'payment_reference' => $data['payment_reference'],
                'total' => $subtotal,
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'product_variant_id' => $item['variant']->id,
                    'product_name' => $item['product']->name,
                    'variant_label' => $item['variant']->label(),
                    'unit_price' => $item['unit_price'],
                    'unit_cost' => $item['product']->cost_price ?? 0,
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            return $order;
        });

        $cart->clear();

        AdminAlertNotification::broadcastToTeam(
            'Nouvelle commande',
            $order->order_number,
            "{$order->customer_name} vient de passer commande ({$order->order_number}).",
            route('orders.show', $order)
        );

        return redirect()->route('shop.orders.confirmation', $order->order_number);
    }
}
