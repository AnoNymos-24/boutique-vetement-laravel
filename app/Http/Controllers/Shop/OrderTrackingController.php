<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    public function confirmation(string $orderNumber): View
    {
        $order = $this->findOwnedOrder($orderNumber);

        return view('shop.order-confirmation', ['order' => $order]);
    }

    public function show(string $orderNumber): View
    {
        $order = $this->findOwnedOrder($orderNumber);

        return view('shop.order-tracking', ['order' => $order]);
    }

    private function findOwnedOrder(string $orderNumber): Order
    {
        $order = Order::where('order_number', $orderNumber)->with('items')->firstOrFail();

        abort_unless($order->customer_id === Auth::guard('customer')->id(), 403);

        return $order;
    }
}
