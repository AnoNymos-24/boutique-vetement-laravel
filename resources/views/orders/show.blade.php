@php
    $steps = [
        ['key' => 'en_attente', 'label' => 'En attente', 'icon' => 'cart', 'date' => $order->created_at],
        ['key' => 'confirmee', 'label' => 'Confirmée', 'icon' => 'check', 'date' => $order->confirmed_at],
        ['key' => 'preparee', 'label' => 'Préparée', 'icon' => 'box', 'date' => $order->prepared_at],
        ['key' => 'expediee', 'label' => 'Expédiée', 'icon' => 'cart', 'date' => $order->shipped_at],
        ['key' => 'livree', 'label' => 'Livrée', 'icon' => 'check', 'date' => $order->delivered_at],
    ];
    $currentIndex = array_search($order->status, array_column($steps, 'key'), true);
@endphp

<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <div class="breadcrumb">
                <a href="{{ route('orders') }}" class="link">Commandes</a>
                <x-icon name="chevron-right" />
                <span>{{ $order->order_number }}</span>
            </div>
            <h1 class="h1">{{ $order->order_number }}</h1>
            <p class="text-muted text-sm">
                Passée le {{ $order->created_at->format('d/m/Y à H:i') }}
                @if ($order->isManualSale())
                    — <span class="badge badge--muted"><x-icon name="cash" /> Vente manuelle</span>
                @endif
            </p>
        </div>

        @if (!$order->isTerminal())
            <form method="POST" action="{{ route('orders.cancel', $order) }}"
                  onsubmit="return confirm('Annuler la commande {{ $order->order_number }} ? Cette action est irréversible.');">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-secondary">
                    <x-icon name="x" /> Annuler la commande
                </button>
            </form>
        @endif
    </div>

    @if ($order->status === 'annulee')
        <div class="status-banner status-banner--error" data-reveal style="margin-bottom: var(--space-xl);">
            Commande annulée le {{ $order->cancelled_at?->format('d/m/Y à H:i') }}.
        </div>
    @else
        <div class="card" data-reveal style="margin-bottom: var(--space-xl);">
            <div class="order-timeline">
                @foreach ($steps as $index => $step)
                    <div class="order-timeline__step {{ $index < $currentIndex ? 'is-done' : ($index === $currentIndex ? 'is-current' : '') }}">
                        <div class="order-timeline__dot">
                            @if ($index < $currentIndex || ($index === $currentIndex && $step['key'] === 'livree'))
                                <x-icon name="check" />
                            @else
                                {{ $index + 1 }}
                            @endif
                        </div>
                        <div class="order-timeline__label">{{ $step['label'] }}</div>
                        @if ($step['date'])
                            <div class="order-timeline__date">{{ $step['date']->format('d/m H:i') }}</div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($order->nextStatus())
                <form method="POST" action="{{ route('orders.advance', $order) }}" style="margin-top: var(--space-2xl); text-align:center;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" /> {{ $order->nextStatusLabel() }}
                    </button>
                </form>
            @endif
        </div>
    @endif

    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--space-xl); margin-bottom: var(--space-xl);">
        <div class="card" data-reveal>
            <div class="card-header">
                <h2 class="card-title">Client</h2>
                @if ($order->customer_id)
                    <a href="{{ route('clients.show', $order->customer_id) }}" class="link">Voir la fiche client</a>
                @endif
            </div>
            <div class="info-row info-row--stack-mobile">
                <span class="info-row__label">Nom</span>
                <span class="font-semibold">{{ $order->customer_name }}</span>
            </div>
            <div class="info-row info-row--stack-mobile">
                <span class="info-row__label">Téléphone</span>
                <span>{{ $order->customer_phone }}</span>
            </div>
            @if ($order->customer_address)
                <div class="info-row info-row--stack-mobile">
                    <span class="info-row__label">Adresse</span>
                    <span>{{ $order->customer_address }}</span>
                </div>
            @endif
        </div>

        <div class="card" data-reveal>
            <div class="card-header">
                <h2 class="card-title">Paiement</h2>
            </div>
            <div class="info-row info-row--stack-mobile">
                <span class="info-row__label">Moyen</span>
                <span>{{ $order->paymentMethodLabel() }}</span>
            </div>
            @unless ($order->payment_method === 'especes')
                <div class="info-row info-row--stack-mobile">
                    <span class="info-row__label">Référence (6 derniers chiffres)</span>
                    <span class="font-mono">{{ $order->payment_reference ?? '—' }}</span>
                </div>
            @endunless
            <div class="info-row info-row--stack-mobile">
                <span class="info-row__label">Statut</span>
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
            </div>

            @if ($order->payment_status === 'en_attente_verification')
                <div class="flex gap-md mt-lg">
                    <form method="POST" action="{{ route('orders.payment', $order) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="action" value="verifie">
                        <button type="submit" class="btn btn-primary btn-sm"><x-icon name="check" /> Confirmer le paiement</button>
                    </form>
                    <form method="POST" action="{{ route('orders.payment', $order) }}"
                          onsubmit="return confirm('Rejeter ce paiement ?');">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="action" value="rejete">
                        <button type="submit" class="btn btn-danger btn-sm"><x-icon name="x" /> Rejeter</button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <div class="card" data-reveal>
        <div class="card-header">
            <h2 class="card-title">Articles commandés</h2>
        </div>

        <div class="table-wrapper" style="border:0; border-radius:0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Prix unitaire</th>
                        <th>Quantité</th>
                        <th class="cell-actions">Sous-total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="cell-primary">
                                {{ $item->product_name }}
                                @if ($item->variant_label)
                                    <span class="text-muted text-sm">— {{ $item->variant_label }}</span>
                                @endif
                            </td>
                            <td class="cell-numeric">{{ $item->unitPriceFormatted() }}</td>
                            <td class="cell-numeric">{{ $item->quantity }}</td>
                            <td class="cell-numeric cell-actions">{{ $item->subtotalFormatted() }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="3" class="cell-primary" style="text-align:right;">Total</td>
                        <td class="cell-numeric cell-actions font-semibold" style="font-size:1.05rem;">{{ $order->totalFormatted() }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @if ($order->note)
        <div class="card" data-reveal style="margin-top: var(--space-xl);">
            <div class="card-header"><h2 class="card-title">Note</h2></div>
            <p class="text-sm">{{ $order->note }}</p>
        </div>
    @endif
</x-app-layout>
