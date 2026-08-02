<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query()
            ->withCount('items')
            ->when($request->search, fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('order_number', 'like', "%{$search}%")
                ->orWhere('customer_name', 'like', "%{$search}%")
                ->orWhere('customer_phone', 'like', "%{$search}%")
            ))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->payment_status, fn ($q, $paymentStatus) => $q->where('payment_status', $paymentStatus))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $kpis = [
            'a_traiter' => Order::where('status', 'en_attente')->count(),
            'a_verifier' => Order::where('payment_status', 'en_attente_verification')->count(),
            'expediees' => Order::where('status', 'expediee')->count(),
            'ca_mois' => Order::where('payment_status', 'verifie')->whereMonth('created_at', now()->month)->sum('total'),
        ];

        return view('orders.index', [
            'orders' => $orders,
            'kpis' => $kpis,
        ]);
    }

    public function show(Order $order)
    {
        $order->load('items.product');

        return view('orders.show', [
            'order' => $order,
        ]);
    }

    public function advanceStatus(Order $order)
    {
        $next = $order->nextStatus();

        if (! $next) {
            return back()->with('error', 'Cette commande est déjà à son statut final.');
        }

        $timestampField = match ($next) {
            'confirmee' => 'confirmed_at',
            'preparee' => 'prepared_at',
            'expediee' => 'shipped_at',
            'livree' => 'delivered_at',
        };

        $order->update([
            'status' => $next,
            $timestampField => now(),
        ]);

        return back()->with('status', "Commande {$order->order_number} : {$order->statusLabel()}.");
    }

    public function cancel(Order $order)
    {
        if ($order->isTerminal()) {
            return back()->with('error', 'Cette commande ne peut plus être annulée.');
        }

        $order->update(['status' => 'annulee', 'cancelled_at' => now()]);

        return back()->with('status', "Commande {$order->order_number} annulée.");
    }

    public function updatePayment(Request $request, Order $order)
    {
        $data = $request->validate([
            'action' => ['required', 'in:verifie,rejete'],
        ]);

        // Les ventes en ligne ne décrémentent pas le stock à la commande (paiement à vérifier
        // manuellement) : le stock n'est retiré qu'à la confirmation du paiement, une seule fois.
        if ($data['action'] === 'verifie' && $order->payment_status !== 'verifie' && $order->channel === 'boutique_en_ligne') {
            DB::transaction(function () use ($order) {
                foreach ($order->items()->whereNotNull('product_variant_id')->get() as $item) {
                    $variant = ProductVariant::find($item->product_variant_id);
                    if (! $variant) {
                        continue;
                    }

                    $newStock = max(0, $variant->stock_quantity - $item->quantity);

                    $variant->stockMovements()->create([
                        'user_id' => Auth::id(),
                        'type' => 'sortie',
                        'quantity_change' => $newStock - $variant->stock_quantity,
                        'stock_after' => $newStock,
                        'note' => "Vente en ligne {$order->order_number} — paiement vérifié",
                    ]);

                    $variant->update(['stock_quantity' => $newStock]);
                }
            });
        }

        $order->update(['payment_status' => $data['action']]);

        $message = $data['action'] === 'verifie'
            ? "Paiement de {$order->order_number} confirmé."
            : "Paiement de {$order->order_number} rejeté.";

        return back()->with('status', $message);
    }
}
