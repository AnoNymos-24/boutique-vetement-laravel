<x-shop-layout>
    <div class="shop-container" style="padding: var(--shop-space-2xl) var(--shop-space-lg) var(--shop-space-3xl); max-width: 720px;">
        <div style="margin-bottom: var(--shop-space-xl);" data-reveal>
            <p class="shop-eyebrow">Suivi de commande</p>
            <h1 class="shop-h1" style="font-size:2rem;">{{ $order->order_number }}</h1>
            <p class="shop-text-muted">Passée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
        </div>

        <div class="cart-summary" style="margin-bottom: var(--shop-space-xl);" data-reveal>
            <x-shop.order-timeline :order="$order" />
        </div>

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
                    <span class="cart-summary__value">{{ number_format($item->subtotal / 100, 3, ',', ' ') }} DT</span>
                </div>
            @endforeach

            <div class="cart-summary__row cart-summary__row--total">
                <span>Total</span>
                <span class="cart-summary__value">{{ $order->totalFormatted() }}</span>
            </div>
        </div>

        @if ($order->payment_status === 'verifie')
            <div class="cart-summary" style="margin-top: var(--shop-space-xl);" data-reveal>
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
