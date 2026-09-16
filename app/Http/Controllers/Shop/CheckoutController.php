<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\StoreSetting;
use App\Notifications\AdminAlertNotification;
use App\Notifications\OrderConfirmationNotification;
use App\Services\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(Cart $cart): View|RedirectResponse
    {
        if ($cart->isEmpty()) {
            return redirect()->route('shop.cart')->with('error', 'Votre panier est vide.');
        }

        $customer = Auth::guard('customer')->user();
        $setting = StoreSetting::current();

        return view('shop.checkout', [
            'items' => $cart->items(),
            'subtotal' => $cart->subtotal(),
            'customer' => $customer,
            'addresses' => $customer->addresses()->orderByDesc('is_default')->get(),
            'paymentMethods' => $setting->enabledPaymentMethods(),
            'setting' => $setting,
        ]);
    }

    public function store(Request $request, Cart $cart): RedirectResponse
    {
        if ($cart->isEmpty()) {
            return redirect()->route('shop.cart')->with('error', 'Votre panier est vide.');
        }

        $enabledMethods = StoreSetting::current()->enabledPaymentMethods();

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_address' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', Rule::in(array_keys($enabledMethods))],
            'payment_reference' => [
                Rule::requiredIf(fn () => ($enabledMethods[$request->payment_method]['requires_reference'] ?? false)),
                'nullable', 'digits:6',
            ],
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
                'payment_reference' => $data['payment_reference'] ?? null,
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

        try {
            $customer->notify(new OrderConfirmationNotification($order));
        } catch (\Throwable $e) {
            // La commande est déjà enregistrée — un échec d'envoi ne doit jamais bloquer le client.
            Log::warning("Échec d'envoi de la confirmation de commande {$order->order_number} : {$e->getMessage()}");
        }

        return redirect()->route('shop.orders.confirmation', $order->order_number);
    }
}
