<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    private function periodRange(string $period): array
    {
        return match ($period) {
            'mois_dernier' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            '30_jours' => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    public function index(Request $request)
    {
        $period = $request->get('period', 'ce_mois');
        [$start, $end] = $this->periodRange($period);

        $revenue = Order::where('payment_status', 'verifie')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total');

        $cogs = (int) OrderItem::whereHas('order', fn ($q) => $q
            ->where('payment_status', 'verifie')
            ->whereBetween('created_at', [$start, $end])
        )->sum(DB::raw('unit_cost * quantity'));

        $expensesTotal = Expense::whereBetween('expense_date', [$start, $end])->sum('amount');
        $grossMargin = $revenue - $cogs;
        $netProfit = $grossMargin - $expensesTotal;

        $pending = Order::where('payment_status', 'en_attente_verification')->sum('total');

        $dailyRevenue = Order::where('payment_status', 'verifie')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as day, SUM(total) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $chartDays = collect();
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $chartDays->push([
                'date' => $date->copy(),
                'total' => (int) ($dailyRevenue[$date->format('Y-m-d')] ?? 0),
            ]);
        }

        $topProducts = OrderItem::whereHas('order', fn ($q) => $q
            ->where('payment_status', 'verifie')
            ->whereBetween('created_at', [$start, $end])
        )
            ->select('product_name')
            ->selectRaw('SUM(subtotal) as revenue')
            ->selectRaw('SUM(quantity) as qty')
            ->groupBy('product_name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $expenses = Expense::whereBetween('expense_date', [$start, $end])
            ->orderByDesc('expense_date')
            ->get();

        return view('finance.index', [
            'period' => $period,
            'start' => $start,
            'end' => $end,
            'kpis' => [
                'revenue' => $revenue,
                'cogs' => $cogs,
                'gross_margin' => $grossMargin,
                'expenses_total' => $expensesTotal,
                'net_profit' => $netProfit,
                'pending' => $pending,
            ],
            'chartDays' => $chartDays,
            'topProducts' => $topProducts,
            'expenses' => $expenses,
        ]);
    }

    public function storeExpense(Request $request)
    {
        $data = $request->validate([
            'category' => ['required', 'in:livraison,hebergement,marketing,commissions,salaires,autre'],
            'label' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $data['amount'] = (int) round($data['amount'] * 100);
        $data['user_id'] = Auth::id();

        Expense::create($data);

        return back()->with('status', 'Dépense enregistrée.');
    }

    public function destroyExpense(Expense $expense)
    {
        $expense->delete();

        return back()->with('status', 'Dépense supprimée.');
    }

    public function export(Request $request)
    {
        $period = $request->get('period', 'ce_mois');
        [$start, $end] = $this->periodRange($period);

        $orders = Order::where('payment_status', 'verifie')
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at')
            ->get();

        $expenses = Expense::whereBetween('expense_date', [$start, $end])->orderBy('expense_date')->get();

        $filename = 'rapport-finance-' . $start->format('Y-m-d') . '-au-' . $end->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($orders, $expenses) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // BOM pour un affichage correct des accents dans Excel

            fputcsv($handle, ['Commandes payées'], ';');
            fputcsv($handle, ['N° commande', 'Date', 'Client', 'Canal', 'Total (€)'], ';');
            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->created_at->format('d/m/Y'),
                    $order->customer_name,
                    $order->isManualSale() ? 'Vente manuelle' : 'Boutique en ligne',
                    number_format($order->total / 100, 2, ',', ''),
                ], ';');
            }

            fputcsv($handle, [], ';');
            fputcsv($handle, ['Dépenses'], ';');
            fputcsv($handle, ['Date', 'Catégorie', 'Libellé', 'Montant (€)'], ';');
            foreach ($expenses as $expense) {
                fputcsv($handle, [
                    $expense->expense_date->format('d/m/Y'),
                    $expense->categoryLabel(),
                    $expense->label,
                    number_format($expense->amount / 100, 2, ',', ''),
                ], ';');
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
