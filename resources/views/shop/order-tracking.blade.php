<x-shop-layout>
    <div class="shop-container" style="padding: var(--shop-space-2xl) var(--shop-space-lg) var(--shop-space-3xl); max-width: 720px;">
        <div style="margin-bottom: var(--shop-space-xl);" data-reveal>
            <p class="shop-eyebrow">Suivi de commande</p>
            <h1 class="shop-h1" style="font-size:2rem;">{{ $order->order_number }}</h1>
            <p class="shop-text-muted">Passée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
        </div>

        <div class="cart-summary" style="margin-bottom: var(--shop-space-xl);" data-reveal>
            <x-shop.order-timeline :order="$order" />

            @if (! in_array($order->status, ['expediee', 'livree', 'annulee'], true))
                <form method="POST" action="{{ route('customer.account.orders.cancel', $order) }}"
                      style="margin-top: var(--shop-space-lg); text-align:center;"
                      onsubmit="return confirm('Annuler la commande {{ $order->order_number }} ?');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="shop-btn shop-btn--secondary shop-btn--sm">
                        <x-icon name="x" /> Annuler ma commande
                    </button>
                </form>
            @endif
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
                    <span class="cart-summary__value">{{ $item->subtotalFormatted() }}</span>
                </div>
            @endforeach

            <div class="cart-summary__row cart-summary__row--total">
                <span>Total</span>
                <span class="cart-summary__value">{{ $order->totalFormatted() }}</span>
            </div>
        </div>

        @if ($order->payment_status === 'verifie')
            <div class="cart-summary" style="margin-top: var(--shop-space-xl);" data-reveal>
                <h3 class="shop-h3">Satisfaite de vos articles ?</h3>
                <p class="shop-text-muted" style="font-size:0.875rem; margin: var(--shop-space-xs) 0 var(--shop-space-md);">Partagez votre avis, il sera visible immédiatement.</p>
                <a href="{{ route('customer.reviews') }}" class="shop-btn shop-btn--secondary shop-btn--sm">
                    <x-icon name="star" /> Laisser un avis
                </a>
            </div>
        @endif
    </div>
</x-shop-layout>
