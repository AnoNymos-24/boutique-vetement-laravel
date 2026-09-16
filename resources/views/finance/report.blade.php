<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rapport financier — {{ $store->name }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;500;600;700&family=Fira+Code:wght@600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --ink: #111827;
            --muted: #6b7280;
            --border: #d1d5db;
            --accent: #059669;
            --row-alt: #f9fafb;
            --total-bg: #ecfdf5;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: var(--space, 24px);
            font-family: 'Fira Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--ink);
            background: #f3f4f6;
            font-size: 14px;
            line-height: 1.5;
        }

        .sheet {
            max-width: 960px;
            margin: 0 auto;
            background: #fff;
            padding: 32px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .toolbar {
            max-width: 960px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .toolbar a, .toolbar button {
            font: inherit;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.6rem 1.1rem;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--ink);
            cursor: pointer;
            text-decoration: none;
        }

        .toolbar button.primary {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }

        .report-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            padding-bottom: 20px;
            margin-bottom: 24px;
            border-bottom: 2px solid var(--ink);
        }

        .report-header__brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .report-header__brand img {
            max-height: 44px;
            width: auto;
        }

        .report-header__store {
            font-size: 1.375rem;
            font-weight: 700;
        }

        .report-header__meta {
            text-align: right;
            font-size: 0.8125rem;
            color: var(--muted);
        }

        h1.report-title {
            font-size: 1.125rem;
            font-weight: 700;
            margin: 0 0 24px;
            color: var(--muted);
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        /* Titres de section : gras, hiérarchie claire, bien espacés */
        h2.report-section {
            font-size: 1.0625rem;
            font-weight: 700;
            margin: 32px 0 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--ink);
        }

        h2.report-section:first-of-type {
            margin-top: 0;
        }

        .report-table-wrap {
            /* Dernier recours seulement — colonnes harmonisées + retour à la
               ligne du texte doivent en principe éviter tout débordement. */
            overflow-x: auto;
        }

        table.report-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            font-size: 0.8125rem;
        }

        /* 1. Colonnes séparées par des lignes verticales + horizontales pour un
           visuel structuré. 2. Padding pour l'air entre texte et bordures. */
        table.report-table th,
        table.report-table td {
            border: 1px solid var(--border);
            padding: 10px 12px;
            text-align: left;
            vertical-align: top;
            /* 3. Le texte qui déborde revient à la ligne au lieu de forcer la
               largeur de la colonne. */
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }

        table.report-table th {
            background: #f3f4f6;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: var(--muted);
        }

        table.report-table tbody tr:nth-child(even) {
            background: var(--row-alt);
        }

        table.report-table .num {
            font-family: 'Fira Code', monospace;
            font-variant-numeric: tabular-nums;
            text-align: right;
            white-space: nowrap;
        }

        table.report-table tfoot td {
            font-weight: 700;
            background: var(--total-bg);
        }

        table.report-table .empty-row td {
            color: var(--muted);
            font-style: italic;
            text-align: center;
        }

        .report-highlight {
            margin-top: 16px;
            padding: 16px 20px;
            background: var(--total-bg);
            border: 1px solid var(--accent);
            border-radius: 8px;
            font-size: 0.9375rem;
        }

        .report-highlight strong {
            font-family: 'Fira Code', monospace;
            font-size: 1.0625rem;
        }

        .report-footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
            font-size: 0.75rem;
            color: var(--muted);
            text-align: center;
        }

        /* 4. Largeurs de colonnes harmonisées par tableau, via des classes
           dédiées plutôt que des styles inline répétés. */
        .col-num { width: 12%; }
        .col-date { width: 12%; }
        .col-client { width: 16%; }
        .col-canal { width: 14%; }
        .col-produits { width: 34%; }
        .col-motif { width: 40%; }
        .col-categorie { width: 18%; }
        .col-libelle { width: 40%; }
        .col-produit { width: 50%; }
        .col-qte { width: 20%; }
        .col-mois { width: 18%; }
        .col-money { width: 12%; }
        .col-money-wide { width: 16%; }

        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .sheet { box-shadow: none; border-radius: 0; padding: 0; max-width: none; }
            table.report-table { font-size: 10.5pt; }
            h2.report-section { break-after: avoid; }
            table.report-table tr { break-inside: avoid; }
        }

        /* Sur téléphone : marge réduite, en-tête empilé, tableaux qui restent
           lisibles sans avoir à défiler grâce au word-wrap + colonnes fixes. */
        @media (max-width: 640px) {
            body { padding: 12px; font-size: 13px; }
            .sheet { padding: 16px; }
            .toolbar { flex-direction: column; margin-bottom: 12px; }
            .toolbar a, .toolbar button { width: 100%; text-align: center; }
            .report-header { flex-direction: column; align-items: flex-start; }
            .report-header__meta { text-align: left; }
            table.report-table th, table.report-table td { padding: 8px; font-size: 0.75rem; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('finance', ['period' => $period]) }}">← Retour à Finance</a>
        <button type="button" class="primary" onclick="window.print()">Imprimer / Enregistrer en PDF</button>
    </div>

    <div class="sheet">
        <div class="report-header">
            <div class="report-header__brand">
                @if ($store->logoUrl())
                    <img src="{{ $store->logoUrl() }}" alt="{{ $store->name }}">
                @endif
                <span class="report-header__store">{{ $store->name }}</span>
            </div>
            <div class="report-header__meta">
                Période : {{ $periodLabel }}<br>
                Généré le {{ now()->translatedFormat('d F Y à H:i') }}
            </div>
        </div>

        <h1 class="report-title">Rapport financier</h1>

        <h2 class="report-section">Commandes payées</h2>
        <div class="report-table-wrap">
            <table class="report-table">
                <colgroup>
                    <col class="col-num"><col class="col-date"><col class="col-client">
                    <col class="col-canal"><col class="col-produits"><col class="col-money">
                </colgroup>
                <thead>
                    <tr>
                        <th>N° commande</th>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Canal</th>
                        <th>Produits</th>
                        <th class="num">Total ({{ $currency['symbol'] }})</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->created_at->format('d/m/Y') }}</td>
                            <td>{{ $order->customer_name }}</td>
                            <td>{{ $order->isManualSale() ? 'Vente manuelle' : 'Boutique en ligne' }}</td>
                            <td>{{ $order->items->map(fn ($item) => ($item->variant_label ? "{$item->product_name} ({$item->variant_label})" : $item->product_name) . " ×{$item->quantity}")->implode(', ') ?: '—' }}</td>
                            <td class="num">{{ number_format($order->total / 100, $currency['decimals'], ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr class="empty-row"><td colspan="6">Aucune commande payée sur cette période.</td></tr>
                    @endforelse
                </tbody>
                @if ($orders->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="5">TOTAL</td>
                            <td class="num">{{ number_format($orders->sum('total') / 100, $currency['decimals'], ',', ' ') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <h2 class="report-section">Remboursements</h2>
        <div class="report-table-wrap">
            <table class="report-table">
                <colgroup>
                    <col class="col-date"><col class="col-num"><col class="col-motif"><col class="col-money">
                </colgroup>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>N° commande</th>
                        <th>Motif</th>
                        <th class="num">Montant ({{ $currency['symbol'] }})</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($refunds as $refund)
                        <tr>
                            <td>{{ $refund->processed_at->format('d/m/Y') }}</td>
                            <td>{{ $refund->order?->order_number ?? '—' }}</td>
                            <td>{{ $refund->reasonLabel() }}</td>
                            <td class="num">{{ number_format($refund->amount / 100, $currency['decimals'], ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr class="empty-row"><td colspan="4">Aucun remboursement sur cette période.</td></tr>
                    @endforelse
                </tbody>
                @if ($refunds->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="3">TOTAL</td>
                            <td class="num">{{ number_format($refunds->sum('amount') / 100, $currency['decimals'], ',', ' ') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <h2 class="report-section">Dépenses</h2>
        <div class="report-table-wrap">
            <table class="report-table">
                <colgroup>
                    <col class="col-date"><col class="col-categorie"><col class="col-libelle"><col class="col-money">
                </colgroup>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Catégorie</th>
                        <th>Libellé</th>
                        <th class="num">Montant ({{ $currency['symbol'] }})</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                            <td>{{ $expense->categoryLabel() }}</td>
                            <td>{{ $expense->label }}</td>
                            <td class="num">{{ number_format($expense->amount / 100, $currency['decimals'], ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr class="empty-row"><td colspan="4">Aucune dépense sur cette période.</td></tr>
                    @endforelse
                </tbody>
                @if ($expenses->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="3">TOTAL</td>
                            <td class="num">{{ number_format($expenses->sum('amount') / 100, $currency['decimals'], ',', ' ') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <h2 class="report-section">Top produits (par chiffre d'affaires)</h2>
        <div class="report-table-wrap">
            <table class="report-table">
                <colgroup>
                    <col class="col-produit"><col class="col-qte"><col class="col-money-wide">
                </colgroup>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th class="num">Quantité vendue</th>
                        <th class="num">CA ({{ $currency['symbol'] }})</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($topProducts as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td class="num">{{ $item->qty }}</td>
                            <td class="num">{{ number_format($item->revenue / 100, $currency['decimals'], ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr class="empty-row"><td colspan="3">Aucune vente sur cette période.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <h2 class="report-section">Bénéfice — comparatif 3 derniers mois</h2>
        <div class="report-table-wrap">
            <table class="report-table">
                <colgroup>
                    <col class="col-mois"><col class="col-money-wide"><col class="col-money-wide">
                    <col class="col-money-wide"><col class="col-money-wide"><col class="col-money-wide">
                </colgroup>
                <thead>
                    <tr>
                        <th>Mois</th>
                        <th class="num">CA encaissé</th>
                        <th class="num">Remboursements</th>
                        <th class="num">COGS</th>
                        <th class="num">Dépenses</th>
                        <th class="num">Bénéfice net</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($monthlyBreakdown as $month)
                        <tr>
                            <td>{{ ucfirst($month['label']) }}</td>
                            <td class="num">{{ number_format($month['revenue'] / 100, $currency['decimals'], ',', ' ') }}</td>
                            <td class="num">{{ number_format($month['refunds'] / 100, $currency['decimals'], ',', ' ') }}</td>
                            <td class="num">{{ number_format($month['cogs'] / 100, $currency['decimals'], ',', ' ') }}</td>
                            <td class="num">{{ number_format($month['expenses'] / 100, $currency['decimals'], ',', ' ') }}</td>
                            <td class="num">{{ number_format($month['net_profit'] / 100, $currency['decimals'], ',', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="report-highlight">
            Résultat de {{ ucfirst($monthlyBreakdown->first()['label']) }} :
            <strong>{{ number_format($monthlyBreakdown->first()['net_profit'] / 100, $currency['decimals'], ',', ' ') }} {{ $currency['symbol'] }}</strong>
        </div>

        <div class="report-footer">
            {{ $store->name }} — Rapport généré automatiquement le {{ now()->translatedFormat('d F Y à H:i') }}
        </div>
    </div>
</body>
</html>
