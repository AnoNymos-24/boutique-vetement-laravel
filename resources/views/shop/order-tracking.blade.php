@php
    $steps = [
        ['key' => 'en_attente', 'label' => 'En attente', 'date' => $order->created_at],
        ['key' => 'confirmee', 'label' => 'Confirmée', 'date' => $order->confirmed_at],
        ['key' => 'preparee', 'label' => 'Préparée', 'date' => $order->prepared_at],
        ['key' => 'expediee', 'label' => 'Expédiée', 'date' => $order->shipped_at],
        ['key' => 'livree', 'label' => 'Livrée', 'date' => $order->delivered_at],
    ];
    $currentIndex = array_search($order->status, array_column($steps, 'key'), true);
@endphp

<x-shop-layout>
    <div class="shop-container" style="padding: var(--shop-space-2xl) var(--shop-space-lg) var(--shop-space-3xl); max-width: 720px;">
        <div style="margin-bottom: var(--shop-space-xl);" data-reveal>
            <p class="shop-eyebrow">Suivi de commande</p>
            <h1 class="shop-h1" style="font-size:2rem;">{{ $order->order_number }}</h1>
            <p class="shop-text-muted">Passée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
        </div>

        @if ($order->status === 'annulee')
            <div class="shop-status-banner shop-status-banner--error" data-reveal>
                Commande annulée le {{ $order->cancelled_at?->format('d/m/Y à H:i') }}.
            </div>
        @else
            <div class="cart-summary" style="margin-bottom: var(--shop-space-xl);" data-reveal>
                <div class="shop-timeline">
                    @foreach ($steps as $index => $step)
                        <div class="shop-timeline__step {{ $index < $currentIndex ? 'is-done' : ($index === $currentIndex ? 'is-current' : '') }}">
                            <div class="shop-timeline__dot">
                                @if ($index < $currentIndex || ($index === $currentIndex && $step['key'] === 'livree'))
                                    <x-icon name="check" />
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </div>
                            <div class="shop-timeline__label">{{ $step['label'] }}</div>
                            @if ($step['date'])
                                <div class="shop-timeline__date">{{ $step['date']->format('d/m H:i') }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="cart-summary" data-reveal>
            <h3 class="shop-h3" style="margin-bottom: var(--shop-space-md);">Détails</h3>
            <div class="shop-info-row">
                <span class="shop-info-row__label">Statut du paiement</span>
                <span class="shop-badge {{ $order->payment_status === 'verifie' ? 'shop-badge--success' : ($order->payment_status === 'rejete' ? '' : 'shop-badge--warning') }}">
                    {{ $order->paymentStatusLabel() }}
                </span>
            </div>
            <div class="shop-info-row">
                <span class="shop-info-row__label">Moyen de paiement</span>
                <span>{{ $order->paymentMethodLabel() }}</span>
            </div>
            <div class="shop-info-row">
                <span class="shop-info-row__label">Livraison</span>
                <span>{{ $order->customer_address }}</span>
            </div>

            @foreach ($order->items as $item)
                <div class="shop-info-row">
                    <span class="shop-info-row__label">{{ $item->product_name }} @if ($item->variant_label) ({{ $item->variant_label }}) @endif × {{ $item->quantity }}</span>
                    <span class="cart-summary__value">{{ number_format($item->subtotal / 100, 2, ',', ' ') }} €</span>
                </div>
            @endforeach

            <div class="cart-summary__row cart-summary__row--total">
                <span>Total</span>
                <span class="cart-summary__value">{{ $order->totalFormatted() }}</span>
            </div>
        </div>

        @if ($order->payment_status === 'verifie')
            <div class="cart-summary" data-reveal>
                <div class="shop-flex shop-justify-between shop-items-center">
                    <div>
                        <h3 class="shop-h3">Satisfaite de vos articles ?</h3>
                        <p class="shop-text-muted" style="font-size:0.875rem;">Partagez votre avis, il sera visible après modération.</p>
                    </div>
                    <a href="{{ route('customer.reviews') }}" class="shop-btn shop-btn--secondary">
                        <x-icon name="star" /> Laisser un avis
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-shop-layout>
