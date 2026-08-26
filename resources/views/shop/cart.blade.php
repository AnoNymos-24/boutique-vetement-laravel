<x-shop-layout>
    <div class="shop-container" style="padding: var(--shop-space-lg) var(--shop-space-lg) var(--shop-space-3xl);">
        <h1 class="shop-h1" style="font-size: 2rem; margin-bottom: var(--shop-space-xl);" data-reveal>Votre panier</h1>

        @if ($items->isEmpty())
            <div class="empty-state" data-reveal>
                <x-icon name="cart" />
                <p class="shop-h3">Votre panier est vide</p>
                <p class="shop-text-muted" style="margin-bottom: var(--shop-space-lg);">Parcourez la boutique pour trouver votre bonheur.</p>
                <a href="{{ route('shop.catalog') }}" class="shop-btn shop-btn--primary">Découvrir la boutique</a>
            </div>
        @else
            <div class="cart-layout" data-reveal>
                <div class="shop-card" style="padding: 0 var(--shop-space-lg);">
                    @foreach ($items as $item)
                        <div class="cart-line">
                            <div class="cart-line__image">
                                @if ($item['product']->images->first())
                                    <img src="{{ $item['product']->images->first()->url() }}" alt="">
                                @endif
                            </div>

                            <div>
                                <a href="{{ route('shop.product', $item['product']) }}" class="cart-line__name">{{ $item['product']->name }}</a>
                                <div class="cart-line__variant">{{ $item['variant']->label() }}</div>
                                <div class="cart-line__price">{{ number_format($item['unit_price'] / 100, 3, ',', ' ') }} DT</div>
                            </div>

                            <div class="cart-line__actions">
                                <form method="POST" action="{{ route('shop.cart.remove', $item['variant']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="shop-btn shop-btn--icon shop-btn--ghost" title="Retirer">
                                        <x-icon name="x" />
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('shop.cart.update', $item['variant']) }}" class="qty-stepper">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" name="quantity" value="{{ $item['quantity'] - 1 }}" {{ $item['quantity'] <= 1 ? 'disabled' : '' }}>−</button>
                                    <span>{{ $item['quantity'] }}</span>
                                    <button type="submit" name="quantity" value="{{ $item['quantity'] + 1 }}" {{ $item['quantity'] >= $item['variant']->stock_quantity ? 'disabled' : '' }}>+</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="cart-summary">
                    <h3 class="shop-h3" style="margin-bottom: var(--shop-space-md);">Récapitulatif</h3>
                    <div class="cart-summary__row">
                        <span>Sous-total</span>
                        <span class="cart-summary__value">{{ number_format($subtotal / 100, 3, ',', ' ') }} DT</span>
                    </div>
                    <div class="cart-summary__row cart-summary__row--total">
                        <span>Total</span>
                        <span class="cart-summary__value">{{ number_format($subtotal / 100, 3, ',', ' ') }} DT</span>
                    </div>

                    @auth('customer')
                        <a href="{{ route('shop.checkout') }}" class="shop-btn shop-btn--primary shop-btn--full" style="margin-top: var(--shop-space-lg);">
                            Passer la commande <x-icon name="arrow-right" />
                        </a>
                    @else
                        <button type="button" class="shop-btn shop-btn--primary shop-btn--full" style="margin-top: var(--shop-space-lg);" x-data
                                @click="$dispatch('open-modal', 'customer-login')">
                            Se connecter pour commander <x-icon name="arrow-right" />
                        </button>
                        <p class="shop-hint" style="text-align:center;">Un compte est nécessaire pour commander et suivre votre livraison.</p>
                    @endauth
                    <a href="{{ route('shop.catalog') }}" class="shop-btn shop-btn--ghost shop-btn--full" style="margin-top: var(--shop-space-sm);">
                        Continuer mes achats
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-shop-layout>
