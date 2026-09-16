<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Notifications\AdminAlertNotification;
use App\Notifications\OrderCancelledNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function show(): View
    {
        $customer = Auth::guard('customer')->user();

        $recentOrders = $customer->orders()->latest()->limit(3)->get();

        return view('shop.account.dashboard', [
            'customer' => $customer,
            'recentOrders' => $recentOrders,
            'ordersCount' => $customer->orders()->count(),
            'wishlistCount' => $customer->wishlists()->count(),
        ]);
    }

    public function orders(): View
    {
        $customer = Auth::guard('customer')->user();

        $orders = $customer->orders()->withCount('items')->latest()->paginate(10);

        return view('shop.account.orders', [
            'customer' => $customer,
            'orders' => $orders,
        ]);
    }

    public function orderShow(Order $order): View
    {
        abort_unless($order->customer_id === Auth::guard('customer')->id(), 403);

        $order->load('items');

        return view('shop.account.order-show', [
            'customer' => Auth::guard('customer')->user(),
            'order' => $order,
        ]);
    }

    /**
     * Annulation à l'initiative de la cliente — distincte de OrderController::cancel()
     * (staff), avec sa propre vérification de propriété et sa propre limite : on ne
     * peut plus revenir en arrière une fois le colis remis au transporteur.
     */
    public function cancelOrder(Order $order): RedirectResponse
    {
        abort_unless($order->customer_id === Auth::guard('customer')->id(), 403);

        if (in_array($order->status, ['expediee', 'livree', 'annulee'], true)) {
            return back()->with('error', 'Cette commande ne peut plus être annulée : elle est déjà expédiée.');
        }

        $order->update(['status' => 'annulee', 'cancelled_at' => now()]);

        ActivityLog::record('order.cancelled_by_customer', "Commande {$order->order_number} annulée par la cliente.", $order);

        AdminAlertNotification::broadcastToTeam(
            'Action importante',
            "Commande {$order->order_number} annulée par la cliente",
            "{$order->customer_name} a annulé sa commande {$order->order_number}.",
            route('orders.show', $order)
        );

        try {
            $order->customer->notify(new OrderCancelledNotification($order));
        } catch (\Throwable $e) {
            Log::warning("Échec d'envoi de la confirmation d'annulation {$order->order_number} : {$e->getMessage()}");
        }

        return back()->with('status', "Commande {$order->order_number} annulée.");
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email,' . $customer->id],
            'phone' => ['required', 'string', 'max:30', 'unique:customers,phone,' . $customer->id],
        ]);

        $customer->update($data);

        return back()->with('status', 'Profil mis à jour.');
    }
}
