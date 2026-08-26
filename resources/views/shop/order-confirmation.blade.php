<x-shop-layout>
    <div class="shop-container" style="padding: var(--shop-space-2xl) var(--shop-space-lg) var(--shop-space-3xl); max-width: 720px;">
        <div id="order-confirmation-celebrate" style="text-align:center; margin-bottom: var(--shop-space-2xl);">
            <div data-celebrate-badge style="width:72px; height:72px; border-radius:50%; background: var(--shop-color-primary); display:flex; align-items:center; justify-content:center; margin: 0 auto var(--shop-space-lg); opacity:0;">
                <x-icon name="check" style="width:32px;height:32px; color:#fff;" />
            </div>
            <h1 class="shop-h1" style="font-size:2rem; opacity:0;" data-celebrate-item>Merci pour votre commande !</h1>
            <p class="shop-text-muted" style="margin-top: var(--shop-space-sm); opacity:0;" data-celebrate-item>
                Commande <strong>{{ $order->order_number }}</strong> enregistrée. Nous vérifions votre paiement et vous tiendrons informée par téléphone.
            </p>
        </div>

        <div class="cart-summary" data-reveal>
            <h3 class="shop-h3" style="margin-bottom: var(--shop-space-md);">Récapitulatif</h3>
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

            <div class="shop-info-row" style="margin-top: var(--shop-space-md);">
                <span class="shop-info-row__label">Paiement</span>
                <span>{{ $order->paymentMethodLabel() }} — réf. {{ $order->payment_reference }}</span>
            </div>
            <div class="shop-info-row">
                <span class="shop-info-row__label">Livraison</span>
                <span>{{ $order->customer_address }}</span>
            </div>
        </div>

        <div class="shop-flex shop-gap-md" style="margin-top: var(--shop-space-xl); justify-content:center;" data-reveal>
            <a href="{{ route('shop.orders.tracking', $order->order_number) }}" class="shop-btn shop-btn--primary">
                Suivre ma commande
            </a>
            <a href="{{ route('shop.catalog') }}" class="shop-btn shop-btn--secondary">
                Continuer mes achats
            </a>
        </div>
    </div>
</x-shop-layout>
