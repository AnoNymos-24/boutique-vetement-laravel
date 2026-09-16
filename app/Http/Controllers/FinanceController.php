<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Refund;
use App\Models\StoreSetting;
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

        $revenue = Order::verifiedAndActive()
            ->whereBetween('created_at', [$start, $end])
            ->sum('total');

        // Remboursements comptés sur leur propre date d'enregistrement (processed_at),
        // pas sur la date de la commande d'origine — un remboursement traité ce mois-ci
        // pour une vente du mois dernier pèse sur CE mois, pas sur l'historique déjà
        // clos (convention comptable standard). Voir Refund/ComplaintController::refund().
        $refundsTotal = (int) Refund::whereBetween('processed_at', [$start, $end])->sum('amount');
        $refundsCount = Refund::whereBetween('processed_at', [$start, $end])->count();
        $netRevenue = $revenue - $refundsTotal;

        $cogs = (int) OrderItem::whereHas('order', fn ($q) => $q
            ->verifiedAndActive()
            ->whereBetween('created_at', [$start, $end])
        )->sum(DB::raw('unit_cost * quantity'));

        $expensesTotal = Expense::whereBetween('expense_date', [$start, $end])->sum('amount');
        $grossMargin = $netRevenue - $cogs;
        $netProfit = $grossMargin - $expensesTotal;

        // Exclut les commandes annulées avant vérification : rien à vérifier pour
        // une commande que la cliente ou l'équipe a déjà annulée entre-temps.
        $pending = Order::where('payment_status', 'en_attente_verification')
            ->where('status', '!=', 'annulee')
            ->sum('total');

        $dailyRevenue = Order::verifiedAndActive()
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
            ->verifiedAndActive()
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
                'refunds' => $refundsTotal,
                'refunds_count' => $refundsCount,
                'net_revenue' => $netRevenue,
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

    /**
     * Même formule que index() (CA net des remboursements, moins COGS, moins
     * dépenses) mais isolée ici pour être rejouée sur 3 mois glissants dans
     * l'export CSV, sans toucher au calcul déjà en place (et éprouvé) du tableau de bord.
     */
    private function periodFinancials(Carbon $start, Carbon $end): array
    {
        $revenue = (int) Order::verifiedAndActive()
            ->whereBetween('created_at', [$start, $end])
            ->sum('total');

        $refunds = (int) Refund::whereBetween('processed_at', [$start, $end])->sum('amount');

        $cogs = (int) OrderItem::whereHas('order', fn ($q) => $q
            ->verifiedAndActive()
            ->whereBetween('created_at', [$start, $end])
        )->sum(DB::raw('unit_cost * quantity'));

        $expenses = (int) Expense::whereBetween('expense_date', [$start, $end])->sum('amount');

        return [
            'revenue' => $revenue,
            'refunds' => $refunds,
            'cogs' => $cogs,
            'expenses' => $expenses,
            'net_profit' => ($revenue - $refunds) - $cogs - $expenses,
        ];
    }

    /**
     * Rassemble toutes les données du rapport financier (commandes payées avec
     * leurs articles, remboursements, dépenses, top produits, comparatif 3 mois)
     * — partagé entre l'export CSV et la page HTML imprimable pour qu'ils
     * affichent toujours exactement les mêmes chiffres.
     */
    private function reportData(Carbon $start, Carbon $end): array
    {
        $orders = Order::with('items')
            ->verifiedAndActive()
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at')
            ->get();

        $expenses = Expense::whereBetween('expense_date', [$start, $end])->orderBy('expense_date')->get();
        $refunds = Refund::with(['order', 'complaint'])->whereBetween('processed_at', [$start, $end])->orderBy('processed_at')->get();

        $topProducts = OrderItem::whereHas('order', fn ($q) => $q
            ->verifiedAndActive()
            ->whereBetween('created_at', [$start, $end])
        )
            ->select('product_name')
            ->selectRaw('SUM(subtotal) as revenue')
            ->selectRaw('SUM(quantity) as qty')
            ->groupBy('product_name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // "Résultat du mois" ancré sur la fin de la période SÉLECTIONNÉE ($end),
        // pas sur now() — sinon un rapport généré pour "Mois dernier" affichait
        // quand même le bénéfice du mois EN COURS en tête de tableau, incohérent
        // avec le reste du rapport (commandes/remboursements/dépenses, eux bien
        // scopés sur "mois dernier"). Pour "30 derniers jours" (pas un mois civil
        // propre), $end retombe sur aujourd'hui donc sur le mois en cours — un
        // repli raisonnable.
        $monthlyBreakdown = collect(range(0, 2))->map(function ($i) use ($end) {
            $monthStart = $end->copy()->subMonthsNoOverflow($i)->startOfMonth();
            $monthEnd = $end->copy()->subMonthsNoOverflow($i)->endOfMonth();

            return array_merge(
                ['label' => $monthStart->translatedFormat('F Y')],
                $this->periodFinancials($monthStart, $monthEnd)
            );
        });

        return [
            'orders' => $orders,
            'expenses' => $expenses,
            'refunds' => $refunds,
            'topProducts' => $topProducts,
            'monthlyBreakdown' => $monthlyBreakdown,
            'currency' => StoreSetting::current()->currencyMeta(),
            'store' => StoreSetting::current(),
            'periodLabel' => $start->translatedFormat('d F Y') . ' au ' . $end->translatedFormat('d F Y'),
        ];
    }

    public function report(Request $request)
    {
        $period = $request->get('period', 'ce_mois');
        [$start, $end] = $this->periodRange($period);

        return view('finance.report', array_merge(
            $this->reportData($start, $end),
            ['period' => $period, 'start' => $start, 'end' => $end]
        ));
    }

    public function export(Request $request)
    {
        $period = $request->get('period', 'ce_mois');
        [$start, $end] = $this->periodRange($period);

        [
            'orders' => $orders,
            'expenses' => $expenses,
            'refunds' => $refunds,
            'topProducts' => $topProducts,
            'monthlyBreakdown' => $monthlyBreakdown,
            'currency' => $currency,
            'store' => $store,
            'periodLabel' => $periodLabel,
        ] = $this->reportData($start, $end);

        $filename = 'rapport-finance-' . $start->format('Y-m-d') . '-au-' . $end->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($orders, $expenses, $refunds, $topProducts, $monthlyBreakdown, $currency, $store, $periodLabel) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // BOM pour un affichage correct des accents dans Excel

            $money = fn (int $cents) => number_format($cents / 100, $currency['decimals'], ',', ' ');
            $rule = fn () => fputcsv($handle, ['════════════════════════════════════════'], ';');
            $section = function (string $title) use ($handle, $rule) {
                fputcsv($handle, [], ';');
                $rule();
                fputcsv($handle, [mb_strtoupper($title)], ';');
                $rule();
            };

            // En-tête du document — identifie le document hors contexte (imprimé,
            // partagé par e-mail) sans dépendre du nom du fichier.
            fputcsv($handle, [$store->name . ' — Rapport financier'], ';');
            fputcsv($handle, ['Période : ' . $periodLabel], ';');
            fputcsv($handle, ['Généré le : ' . now()->translatedFormat('d F Y à H:i')], ';');

            $section('Commandes payées');
            fputcsv($handle, ['N° commande', 'Date', 'Client', 'Canal', 'Produits', "Total ({$currency['symbol']})"], ';');
            foreach ($orders as $order) {
                $products = $order->items->map(function ($item) {
                    $label = $item->variant_label ? "{$item->product_name} ({$item->variant_label})" : $item->product_name;

                    return "{$label} ×{$item->quantity}";
                })->implode(', ');

                fputcsv($handle, [
                    $order->order_number,
                    $order->created_at->format('d/m/Y'),
                    $order->customer_name,
                    $order->isManualSale() ? 'Vente manuelle' : 'Boutique en ligne',
                    $products,
                    $money($order->total),
                ], ';');
            }
            fputcsv($handle, ['', '', '', '', 'TOTAL', $money($orders->sum('total'))], ';');

            $section('Remboursements');
            fputcsv($handle, ['Date', 'N° commande', 'Motif', "Montant ({$currency['symbol']})"], ';');
            foreach ($refunds as $refund) {
                fputcsv($handle, [
                    $refund->processed_at->format('d/m/Y'),
                    $refund->order?->order_number ?? '—',
                    $refund->reasonLabel(),
                    $money($refund->amount),
                ], ';');
            }
            fputcsv($handle, ['', '', 'TOTAL', $money($refunds->sum('amount'))], ';');

            $section('Dépenses');
            fputcsv($handle, ['Date', 'Catégorie', 'Libellé', "Montant ({$currency['symbol']})"], ';');
            foreach ($expenses as $expense) {
                fputcsv($handle, [
                    $expense->expense_date->format('d/m/Y'),
                    $expense->categoryLabel(),
                    $expense->label,
                    $money($expense->amount),
                ], ';');
            }
            fputcsv($handle, ['', '', 'TOTAL', $money($expenses->sum('amount'))], ';');

            $section('Top produits (par chiffre d\'affaires)');
            fputcsv($handle, ['Produit', 'Quantité vendue', "CA ({$currency['symbol']})"], ';');
            foreach ($topProducts as $item) {
                fputcsv($handle, [$item->product_name, $item->qty, $money($item->revenue)], ';');
            }

            $section('Bénéfice — comparatif 3 derniers mois');
            fputcsv($handle, ['Mois', "CA encaissé ({$currency['symbol']})", 'Remboursements', 'COGS', 'Dépenses', 'Bénéfice net'], ';');
            foreach ($monthlyBreakdown as $month) {
                fputcsv($handle, [
                    ucfirst($month['label']),
                    $money($month['revenue']),
                    $money($month['refunds']),
                    $money($month['cogs']),
                    $money($month['expenses']),
                    $money($month['net_profit']),
                ], ';');
            }
            fputcsv($handle, [], ';');
            fputcsv($handle, ['Résultat de ' . ucfirst($monthlyBreakdown->first()['label']) . ' : ' . $money($monthlyBreakdown->first()['net_profit']) . ' ' . $currency['symbol']], ';');

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
