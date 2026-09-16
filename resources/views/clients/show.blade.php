<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <div class="breadcrumb">
                <a href="{{ route('clients') }}" class="link">Clients</a>
                <x-icon name="chevron-right" />
                <span>{{ $customer->name }}</span>
            </div>
            <h1 class="h1">{{ $customer->name }}</h1>
            <p class="text-muted text-sm">Client depuis {{ $customer->created_at->translatedFormat('d F Y') }}</p>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card" style="--kpi-accent: var(--color-primary)">
            <div class="kpi-card__label"><x-icon name="cart" /> Commandes</div>
            <div class="kpi-card__value" data-count-to="{{ $customer->orders_count }}">0</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-accent)">
            <div class="kpi-card__label"><x-icon name="cash" /> Total dépensé</div>
            <div class="kpi-card__value" data-count-to="{{ ($customer->total_spent ?? 0) / 100 }}" data-count-decimals="{{ $currency['decimals'] }}" data-count-suffix=" {{ $currency['symbol'] }}">{{ number_format(0, $currency['decimals']) }} {{ $currency['symbol'] }}</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: #eab308">
            <div class="kpi-card__label"><x-icon name="star" /> Segment</div>
            <div class="kpi-card__value" style="font-size:1.25rem;">{{ $customer->segmentLabel() }}</div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: var(--space-xl); margin-bottom: var(--space-xl);">
        <div class="card" data-reveal>
            <div class="card-header">
                <h2 class="card-title">Coordonnées</h2>
            </div>
            <div class="info-row">
                <span class="info-row__label">Téléphone</span>
                <span class="font-mono">{{ $customer->phone }}</span>
            </div>
            @if ($customer->email)
                <div class="info-row">
                    <span class="info-row__label">Email</span>
                    <span>{{ $customer->email }}</span>
                </div>
            @endif
            @if ($customer->address)
                <div class="info-row">
                    <span class="info-row__label">Adresse</span>
                    <span>{{ $customer->address }}</span>
                </div>
            @endif
        </div>

        <div class="card" data-reveal>
            <div class="card-header">
                <h2 class="card-title">Note interne</h2>
            </div>
            <form method="POST" action="{{ route('clients.note', $customer) }}">
                @csrf
                @method('PATCH')
                <div class="form-group">
                    <textarea name="note" class="form-textarea" rows="3" placeholder="Préférences, remarques sur ce client…">{{ old('note', $customer->note) }}</textarea>
                </div>
                <button type="submit" class="btn btn-secondary btn-sm"><x-icon name="check" /> Enregistrer</button>
            </form>
        </div>
    </div>

    <div class="card" data-reveal>
        <div class="card-header">
            <h2 class="card-title">Historique des commandes</h2>
        </div>

        @if ($orders->isEmpty())
            <p class="text-muted text-sm">Aucune commande pour ce client.</p>
        @else
            <div class="table-wrapper" style="border:0; border-radius:0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Commande</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Paiement</th>
                            <th class="cell-actions">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td class="cell-primary font-mono">
                                    <a href="{{ route('orders.show', $order) }}" class="link">{{ $order->order_number }}</a>
                                </td>
                                <td class="cell-muted text-sm">{{ $order->created_at->format('d/m/Y') }}</td>
                                <td>
                                    @switch($order->status)
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
                                    @if ($order->payment_status === 'verifie')
                                        <span class="badge badge--success"><x-icon name="check" /> Payé</span>
                                    @else
                                        <span class="badge badge--warning">{{ $order->paymentStatusLabel() }}</span>
                                    @endif
                                </td>
                                <td class="cell-numeric cell-actions font-semibold">{{ $order->totalFormatted() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
