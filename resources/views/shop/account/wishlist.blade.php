<x-shop-layout>
    <div class="shop-container" style="padding: var(--shop-space-lg) var(--shop-space-lg) var(--shop-space-3xl);">
        <x-shop.account-layout active="wishlist">
            <div style="margin-bottom: var(--shop-space-lg);" data-reveal>
                <p class="shop-eyebrow">Favoris</p>
                <p class="shop-text-muted">Les articles que vous gardez à l'œil.</p>
            </div>

            @if ($products->isEmpty())
                <div class="account-card shop-card empty-state" style="margin-bottom:0;" data-reveal>
                    <x-icon name="heart" />
                    <p class="shop-h3">Aucun favori pour le moment</p>
                    <p class="shop-text-muted" style="margin-bottom: var(--shop-space-lg);">Ajoutez des articles depuis la boutique.</p>
                    <a href="{{ route('shop.catalog') }}" class="shop-btn shop-btn--primary">Découvrir la boutique</a>
                </div>
            @else
                <div class="wishlist-grid">
                    @foreach ($products as $product)
                        <x-shop.product-card :product="$product" :favorited="true" />
                    @endforeach
                </div>
            @endif
        </x-shop.account-layout>
    </div>
</x-shop-layout>
