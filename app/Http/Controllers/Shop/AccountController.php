<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
