<x-shop-layout>
    <div class="shop-container" style="padding: var(--shop-space-lg) var(--shop-space-lg) var(--shop-space-3xl);">
        <x-shop.account-layout active="orders">
            <div class="shop-flex shop-gap-sm shop-items-center" style="margin-bottom: var(--shop-space-md);">
                <a href="{{ route('customer.account.orders') }}" class="shop-btn shop-btn--ghost shop-btn--sm">Mes commandes</a>
            </div>

            <div class="account-card shop-card" data-reveal>
                <div class="shop-flex shop-justify-between shop-items-center" style="margin-bottom: var(--shop-space-md);">
                    <div>
                        <h3 class="shop-h3">{{ $order->order_number }}</h3>
                        <p class="shop-text-muted" style="font-size:0.8125rem;">Passée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                </div>

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

            <div class="account-card shop-card" data-reveal>
                <h3 class="shop-h3" style="margin-bottom: var(--shop-space-md);">Articles</h3>
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

                <div class="shop-info-row" style="margin-top: var(--shop-space-md);">
                    <span class="shop-info-row__label">Livraison</span>
                    <span>{{ $order->customer_address }}</span>
                </div>
                <div class="shop-info-row">
                    <span class="shop-info-row__label">Paiement</span>
                    <span>{{ $order->paymentMethodLabel() }} — {{ $order->paymentStatusLabel() }}</span>
                </div>
            </div>

            @if ($order->payment_status === 'verifie')
                <div class="account-card shop-card" data-reveal>
                    <h3 class="shop-h3">Satisfaite de vos articles ?</h3>
                    <p class="shop-text-muted" style="font-size:0.875rem; margin: var(--shop-space-xs) 0 var(--shop-space-md);">Partagez votre avis, il sera visible immédiatement.</p>
                    <a href="{{ route('customer.reviews') }}" class="shop-btn shop-btn--secondary shop-btn--sm">
                        <x-icon name="star" /> Laisser un avis
                    </a>
                </div>
            @endif
        </x-shop.account-layout>
    </div>
</x-shop-layout>
