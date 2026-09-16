@php
    $count = max($chartDays->count(), 1);
    $maxValue = max($chartDays->max('total'), 1);
    $viewBoxWidth = 1000;
    $viewBoxHeight = 200;
    $baseline = 190;
    $gap = 2;
    $barWidth = max(($viewBoxWidth / $count) - $gap, 2);

    $bars = $chartDays->values()->map(function ($day, $i) use ($barWidth, $gap, $maxValue, $baseline, $currency) {
        $x = $i * ($barWidth + $gap);
        $height = $day['total'] > 0 ? max(($day['total'] / $maxValue) * 170, 3) : 0;

        return [
            'x' => round($x, 2),
            'y' => round($baseline - $height, 2),
            'height' => round($height, 2),
            'label' => $day['date']->translatedFormat('d M'),
            'value' => number_format($day['total'] / 100, $currency['decimals'], ',', ' ') . ' ' . $currency['symbol'],
        ];
    });
@endphp

<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <h1 class="h1">Finance</h1>
            <p class="text-muted text-sm">{{ $start->translatedFormat('d M Y') }} — {{ $end->translatedFormat('d M Y') }}</p>
        </div>
        <div class="flex gap-md page-header__actions">
            <a href="{{ route('finance.report', ['period' => $period]) }}" target="_blank" class="btn btn-secondary">
                <x-icon name="edit" /> Rapport imprimable
            </a>
            <a href="{{ route('finance.export', ['period' => $period]) }}" class="btn btn-secondary">
                <x-icon name="chart" /> Exporter en CSV
            </a>
        </div>
    </div>

    <div class="settings-tabs" data-reveal>
        <a href="{{ route('finance', ['period' => 'ce_mois']) }}" class="settings-tab {{ $period === 'ce_mois' ? 'is-active' : '' }}">Ce mois-ci</a>
        <a href="{{ route('finance', ['period' => 'mois_dernier']) }}" class="settings-tab {{ $period === 'mois_dernier' ? 'is-active' : '' }}">Mois dernier</a>
        <a href="{{ route('finance', ['period' => '30_jours']) }}" class="settings-tab {{ $period === '30_jours' ? 'is-active' : '' }}">30 derniers jours</a>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card" style="--kpi-accent: var(--color-accent)">
            <div class="kpi-card__label"><x-icon name="cash" /> Chiffre d'affaires encaissé</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['revenue'] / 100 }}" data-count-decimals="{{ $currency['decimals'] }}" data-count-suffix=" {{ $currency['symbol'] }}">{{ number_format(0, $currency['decimals']) }} {{ $currency['symbol'] }}</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-destructive)">
            <div class="kpi-card__label"><x-icon name="warning" /> Remboursements</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['refunds'] / 100 }}" data-count-decimals="{{ $currency['decimals'] }}" data-count-suffix=" {{ $currency['symbol'] }}">{{ number_format(0, $currency['decimals']) }} {{ $currency['symbol'] }}</div>
            <div class="kpi-card__trend is-flat">
                <a href="{{ route('complaints') }}" class="link">{{ $kpis['refunds_count'] }} remboursement(s) — voir les réclamations</a>
            </div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-primary)">
            <div class="kpi-card__label"><x-icon name="box" /> Coût des marchandises vendues</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['cogs'] / 100 }}" data-count-decimals="{{ $currency['decimals'] }}" data-count-suffix=" {{ $currency['symbol'] }}">{{ number_format(0, $currency['decimals']) }} {{ $currency['symbol'] }}</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: #eab308">
            <div class="kpi-card__label"><x-icon name="trend-up" /> Marge brute</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['gross_margin'] / 100 }}" data-count-decimals="{{ $currency['decimals'] }}" data-count-suffix=" {{ $currency['symbol'] }}">{{ number_format(0, $currency['decimals']) }} {{ $currency['symbol'] }}</div>
            <div class="kpi-card__trend is-flat">CA net des remboursements moins COGS</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-destructive)">
            <div class="kpi-card__label"><x-icon name="cart" /> Dépenses</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['expenses_total'] / 100 }}" data-count-decimals="{{ $currency['decimals'] }}" data-count-suffix=" {{ $currency['symbol'] }}">{{ number_format(0, $currency['decimals']) }} {{ $currency['symbol'] }}</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-accent)">
            <div class="kpi-card__label"><x-icon name="check" /> Bénéfice net</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['net_profit'] / 100 }}" data-count-decimals="{{ $currency['decimals'] }}" data-count-suffix=" {{ $currency['symbol'] }}">{{ number_format(0, $currency['decimals']) }} {{ $currency['symbol'] }}</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-warning)">
            <div class="kpi-card__label"><x-icon name="warning" /> Paiements en attente</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['pending'] / 100 }}" data-count-decimals="{{ $currency['decimals'] }}" data-count-suffix=" {{ $currency['symbol'] }}">{{ number_format(0, $currency['decimals']) }} {{ $currency['symbol'] }}</div>
            <div class="kpi-card__trend is-flat">
                <a href="{{ route('orders', ['payment_status' => 'en_attente_verification']) }}" class="link">à vérifier</a>
            </div>
        </div>
    </div>

    <div class="card chart-card" data-reveal style="margin-bottom: var(--space-xl);" x-data="{ hovered: null, bars: @js($bars) }">
        <div class="card-header">
            <h2 class="card-title">Chiffre d'affaires encaissé par jour</h2>
        </div>

        <svg class="revenue-chart" viewBox="0 0 {{ $viewBoxWidth }} {{ $viewBoxHeight }}" preserveAspectRatio="none">
            <line class="revenue-chart__baseline" x1="0" y1="{{ $baseline }}" x2="{{ $viewBoxWidth }}" y2="{{ $baseline }}" />
            @foreach ($bars as $i => $bar)
                <rect
                    class="revenue-chart__bar"
                    :class="{ 'is-hovered': hovered === {{ $i }} }"
                    x="{{ $bar['x'] }}" y="{{ $bar['y'] }}"
                    width="{{ $barWidth }}" height="{{ $bar['height'] }}"
                    rx="2"
                />
                <rect
                    class="revenue-chart__hit"
                    x="{{ $bar['x'] }}" y="0"
                    width="{{ $barWidth }}" height="{{ $viewBoxHeight }}"
                    @mouseenter="hovered = {{ $i }}" @mouseleave="hovered = null"
                />
            @endforeach
        </svg>

        <div class="revenue-chart__labels">
            <span>{{ $chartDays->first()['date']->translatedFormat('d M') }}</span>
            <span>{{ $chartDays->last()['date']->translatedFormat('d M') }}</span>
        </div>

        <template x-if="hovered !== null">
            <div class="chart-tooltip" :style="{ left: ((bars[hovered].x + {{ $barWidth }} / 2) / {{ $viewBoxWidth }} * 100) + '%' }">
                <div class="chart-tooltip__date" x-text="bars[hovered].label"></div>
                <div class="chart-tooltip__value" x-text="bars[hovered].value"></div>
            </div>
        </template>
    </div>

    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: var(--space-xl);">
        <div class="card" data-reveal>
            <div class="card-header">
                <h2 class="card-title">Top produits (par CA)</h2>
            </div>

            @if ($topProducts->isEmpty())
                <p class="text-muted text-sm">Aucune vente sur cette période.</p>
            @else
                <div class="table-wrapper" style="border:0; border-radius:0;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Qté vendue</th>
                                <th class="cell-actions">CA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topProducts as $item)
                                <tr>
                                    <td class="cell-primary">{{ $item->product_name }}</td>
                                    <td class="cell-numeric">{{ $item->qty }}</td>
                                    <td class="cell-numeric cell-actions font-semibold">{{ \App\Models\StoreSetting::current()->formatMoney($item->revenue) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="card" data-reveal>
            <div class="card-header">
                <h2 class="card-title">Dépenses</h2>
            </div>

            <form method="POST" action="{{ route('finance.expenses.store') }}" style="margin-bottom: var(--space-xl);">
                @csrf
                <div class="flex gap-md expense-form-row">
                    <div class="form-group" style="flex:1;">
                        <x-input-label for="label" value="Libellé" />
                        <x-text-input id="label" name="label" placeholder="Ex. Livraison Jumia" required />
                    </div>
                    <div class="form-group expense-form-row__amount">
                        <x-input-label for="amount" value="Montant ({{ $currency['symbol'] }})" />
                        <x-text-input id="amount" type="number" step="0.01" min="0" name="amount" required />
                    </div>
                </div>
                <div class="flex gap-md expense-form-row">
                    <div class="form-group" style="flex:1;">
                        <x-input-label for="category" value="Catégorie" />
                        <select id="category" name="category" class="form-select" required>
                            <option value="livraison">Livraison</option>
                            <option value="hebergement">Hébergement</option>
                            <option value="marketing">Marketing</option>
                            <option value="commissions">Commissions de paiement</option>
                            <option value="salaires">Salaires</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                    <div class="form-group expense-form-row__date">
                        <x-input-label for="expense_date" value="Date" />
                        <x-text-input id="expense_date" type="date" name="expense_date" :value="now()->format('Y-m-d')" required />
                    </div>
                </div>
                <button type="submit" class="btn btn-secondary btn-sm"><x-icon name="plus" /> Ajouter la dépense</button>
            </form>

            @if ($expenses->isEmpty())
                <p class="text-muted text-sm">Aucune dépense enregistrée sur cette période.</p>
            @else
                <div style="display:flex; flex-direction:column; gap:2px;">
                    @foreach ($expenses as $expense)
                        <div class="flex items-center justify-between expense-row" style="padding: var(--space-sm) 0; border-bottom: 1px solid var(--color-muted);">
                            <div class="expense-row__info">
                                <span class="font-semibold text-sm">{{ $expense->label }}</span>
                                <span class="badge badge--muted" style="margin-left: var(--space-sm);">{{ $expense->categoryLabel() }}</span>
                                <div class="text-xs text-muted">{{ $expense->expense_date->format('d/m/Y') }}</div>
                            </div>
                            <div class="flex items-center gap-md" style="flex-shrink:0;">
                                <span class="font-mono font-semibold">{{ $expense->amountFormatted() }}</span>
                                <form method="POST" action="{{ route('finance.expenses.destroy', $expense) }}"
                                      onsubmit="return confirm('Supprimer cette dépense ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-icon" title="Supprimer"><x-icon name="trash" /></button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
