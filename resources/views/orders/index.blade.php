<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <h1 class="h1">Commandes</h1>
            <p class="text-muted text-sm">{{ $orders->total() }} commande(s) au total</p>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card" style="--kpi-accent: var(--color-warning)">
            <div class="kpi-card__label"><x-icon name="cart" /> À traiter</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['a_traiter'] }}">0</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-primary)">
            <div class="kpi-card__label"><x-icon name="cash" /> Paiement à vérifier</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['a_verifier'] }}">0</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-accent)">
            <div class="kpi-card__label"><x-icon name="box" /> Expédiées</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['expediees'] }}">0</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: #eab308">
            <div class="kpi-card__label"><x-icon name="chart" /> CA du mois (payé)</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['ca_mois'] / 100 }}" data-count-decimals="0" data-count-suffix=" {{ $currency['symbol'] }}">0 {{ $currency['symbol'] }}</div>
        </div>
    </div>

    <div data-reveal>
        <form method="GET" class="table-toolbar">
            <div class="search-input" style="max-width:280px;">
                <x-icon name="search" />
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="N° commande, client, téléphone…">
            </div>

            <div class="table-filters">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Tout statut</option>
                    <option value="en_attente" @selected(request('status') === 'en_attente')>En attente</option>
                    <option value="confirmee" @selected(request('status') === 'confirmee')>Confirmée</option>
                    <option value="preparee" @selected(request('status') === 'preparee')>Préparée</option>
                    <option value="expediee" @selected(request('status') === 'expediee')>Expédiée</option>
                    <option value="livree" @selected(request('status') === 'livree')>Livrée</option>
                    <option value="annulee" @selected(request('status') === 'annulee')>Annulée</option>
                </select>

                <select name="payment_status" class="form-select" onchange="this.form.submit()">
                    <option value="">Tout paiement</option>
                    <option value="en_attente_verification" @selected(request('payment_status') === 'en_attente_verification')>À vérifier</option>
                    <option value="verifie" @selected(request('payment_status') === 'verifie')>Payé</option>
                    <option value="rejete" @selected(request('payment_status') === 'rejete')>Rejeté</option>
                </select>

                <button type="submit" class="btn btn-secondary btn-sm">Filtrer</button>
                @if (request()->anyFilled(['search', 'status', 'payment_status']))
                    <a href="{{ route('orders') }}" class="btn btn-ghost btn-sm">Réinitialiser</a>
                @endif
            </div>
        </form>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Client</th>
                        <th>Articles</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Paiement</th>
                        <th>Date</th>
                        <th class="cell-actions"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td class="cell-primary font-mono">
                                {{ $order->order_number }}
                                @if ($order->isManualSale())
                                    <div class="text-xs text-muted" style="font-family:var(--font-body);"><x-icon name="cash" style="width:11px;height:11px;" /> Vente manuelle</div>
                                @endif
                            </td>
                            <td>
                                {{ $order->customer_name }}
                                <div class="text-xs text-muted">{{ $order->customer_phone }}</div>
                            </td>
                            <td class="cell-numeric">{{ $order->items_count }}</td>
                            <td class="cell-numeric font-semibold">{{ $order->totalFormatted() }}</td>
                            <td>
                                @switch($order->status)
                                    @case('en_attente')
                                        <span class="badge badge--warning">En attente</span>
                                        @break
                                    @case('annulee')
                                        <span class="badge badge--destructive">Annulée</span>
                                        @break
                                    @case('livree')
                                        <span class="badge badge--success"><x-icon name="check" /> Livrée</span>
                                        @break
                                    @default
                                        <span class="badge badge--muted">{{ $order->statusLabel() }}</span>
                                @endswitch
                            </td>
                            <td>
                                @switch($order->payment_status)
                                    @case('verifie')
                                        <span class="badge badge--success"><x-icon name="check" /> Payé</span>
                                        @break
                                    @case('rejete')
                                        <span class="badge badge--destructive">Rejeté</span>
                                        @break
                                    @default
                                        <span class="badge badge--warning"><x-icon name="warning" /> À vérifier</span>
                                @endswitch
                            </td>
                            <td class="cell-muted text-sm">{{ $order->created_at->format('d/m/Y') }}</td>
                            <td class="cell-actions">
                                <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary btn-sm">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-muted text-sm" style="text-align:center; padding: var(--space-3xl);">
                                Aucune commande ne correspond à ces filtres.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $orders->appends(request()->query())->links('pagination.custom') }}
        </div>
    </div>
</x-app-layout>
