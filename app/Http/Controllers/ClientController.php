<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->withCount('orders')
            ->withSum(['orders as total_spent' => fn ($q) => $q->where('payment_status', 'verifie')], 'total')
            ->when($request->search, fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
            ))
            ->when($request->segment, function ($q, $segment) {
                return match ($segment) {
                    'nouveau' => $q->having('orders_count', '<=', 1),
                    'regulier' => $q->having('orders_count', '>=', 2)->having('orders_count', '<=', 4),
                    'vip' => $q->having('orders_count', '>=', 5),
                    default => $q,
                };
            })
            ->orderByDesc('total_spent')
            ->paginate(15)
            ->withQueryString();

        $kpis = [
            'total' => Customer::count(),
            'nouveaux_ce_mois' => Customer::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'vip' => Customer::withCount('orders')->having('orders_count', '>=', 5)->get()->count(),
            'panier_moyen' => (int) Customer::query()
                ->join('orders', 'orders.customer_id', '=', 'customers.id')
                ->where('orders.payment_status', 'verifie')
                ->avg('orders.total'),
        ];

        return view('clients.index', [
            'customers' => $customers,
            'kpis' => $kpis,
        ]);
    }

    public function show(Customer $customer)
    {
        $customer->loadCount('orders');
        $customer->loadSum(['orders as total_spent' => fn ($q) => $q->where('payment_status', 'verifie')], 'total');
        $orders = $customer->orders()->latest()->get();

        return view('clients.show', [
            'customer' => $customer,
            'orders' => $orders,
        ]);
    }

    public function updateNote(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $customer->update($data);

        return back()->with('status', 'Note enregistrée.');
    }
}
