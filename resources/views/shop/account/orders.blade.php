<x-shop-layout>
    <div class="shop-container" style="padding: var(--shop-space-lg) var(--shop-space-lg) var(--shop-space-3xl);">
        <x-shop.account-layout active="orders">
            <div class="account-card">
                <h3 class="shop-h3" style="margin-bottom: var(--shop-space-md);">Mes commandes</h3>

                @forelse ($orders as $order)
                    <div class="account-order-row">
                        <div>
                            <div class="account-order-row__number">{{ $order->order_number }}</div>
                            <div class="shop-text-muted" style="font-size:0.8125rem;">
                                {{ $order->created_at->format('d/m/Y') }} — {{ $order->items_count }} article(s) — {{ $order->statusLabel() }}
                            </div>
                        </div>
                        <div class="shop-flex shop-gap-md shop-items-center">
                            <span class="cart-summary__value">{{ $order->totalFormatted() }}</span>
                            <a href="{{ route('customer.account.orders.show', $order) }}" class="shop-btn shop-btn--ghost shop-btn--sm">Détails</a>
                        </div>
                    </div>
                @empty
                    <p class="shop-text-muted">Vous n'avez pas encore passé de commande.</p>
                    <a href="{{ route('shop.catalog') }}" class="shop-btn shop-btn--primary" style="margin-top: var(--shop-space-md);">Découvrir la boutique</a>
                @endforelse

                {{ $orders->links('pagination.shop') }}
            </div>
        </x-shop.account-layout>
    </div>
</x-shop-layout>
