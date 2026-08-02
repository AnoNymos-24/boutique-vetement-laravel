@php
    $avgRating = $reviews->isNotEmpty() ? round($reviews->avg('rating'), 1) : null;
    $variantsForJs = $product->variants->map(fn ($v) => [
        'id' => $v->id,
        'size' => $v->size,
        'color' => $v->color,
        'stock' => $v->stock_quantity,
        'label' => $v->label(),
    ]);
@endphp

<x-shop-layout>
    <div class="shop-container" style="padding: var(--shop-space-lg) var(--shop-space-lg) var(--shop-space-3xl);">
        <div class="shop-flex shop-gap-sm shop-text-muted" style="font-size:0.8125rem; margin-bottom: var(--shop-space-lg);" data-reveal>
            <a href="{{ route('shop.catalog') }}">Boutique</a>
            <span>/</span>
            <a href="{{ route('shop.catalog.category', $product->category) }}">{{ $product->category->name }}</a>
            <span>/</span>
            <span>{{ $product->name }}</span>
        </div>

        <div class="pdp-layout" x-data="variantSelector(@js($variantsForJs))" data-reveal>
            <div class="pdp-gallery">
                <div class="pdp-gallery__main">
                    @if ($product->images->first())
                        <img src="{{ $product->images->first()->url() }}" alt="{{ $product->name }}" id="pdp-main-image">
                    @endif
                </div>
                @if ($product->images->count() > 1)
                    <div class="pdp-gallery__thumbs">
                        @foreach ($product->images as $index => $image)
                            <button type="button" class="pdp-gallery__thumb {{ $index === 0 ? 'is-active' : '' }}"
                                    onclick="document.getElementById('pdp-main-image').src = this.querySelector('img').src;
                                             document.querySelectorAll('.pdp-gallery__thumb').forEach(t => t.classList.remove('is-active'));
                                             this.classList.add('is-active');">
                                <img src="{{ $image->url() }}" alt="">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="pdp-buybox">
                <div class="shop-flex shop-justify-between shop-items-center">
                    <div class="pdp-buybox__category">{{ $product->category->name }}</div>
                    <x-shop.favorite-button :product="$product" :inline="true" />
                </div>
                <h1 class="pdp-buybox__name">{{ $product->name }}</h1>

                @if ($avgRating)
                    <div class="pdp-buybox__rating">
                        <x-star-rating :rating="round($avgRating)" />
                        <span class="shop-text-muted">{{ $avgRating }} ({{ $reviews->count() }} avis)</span>
                    </div>
                @endif

                <div class="pdp-buybox__prices">
                    <span class="pdp-buybox__price">{{ $product->priceFormatted() }}</span>
                    @if ($product->compare_at_price)
                        <span class="pdp-buybox__price--compare">{{ number_format($product->compare_at_price / 100, 2, ',', ' ') }} €</span>
                    @endif
                </div>

                <form method="POST" action="{{ route('shop.cart.add') }}">
                    @csrf
                    <input type="hidden" name="product_variant_id" :value="selectedVariant?.id ?? ''">
                    <input type="hidden" name="quantity" :value="qty">

                    <template x-if="availableSizes.length > 0">
                        <div class="variant-picker">
                            <div class="variant-picker__label"><span>Taille</span></div>
                            <div class="variant-picker__options">
                                <template x-for="s in availableSizes" :key="s">
                                    <button type="button" class="variant-pill" :class="{ 'is-selected': size === s }"
                                            :disabled="stockForSize(s) === 0" @click="selectSize(s)" x-text="s"></button>
                                </template>
                            </div>
                        </div>
                    </template>

                    <template x-if="availableColors.length > 0">
                        <div class="variant-picker">
                            <div class="variant-picker__label"><span>Couleur</span></div>
                            <div class="variant-picker__options">
                                <template x-for="c in availableColors" :key="c">
                                    <button type="button" class="variant-pill" :class="{ 'is-selected': color === c }"
                                            :disabled="stockForColor(c) === 0" @click="selectColor(c)" x-text="c"></button>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div class="pdp-buybox__stock-note">
                        <x-icon name="check" style="width:16px;height:16px;" />
                        <span x-text="stockLabel"></span>
                    </div>

                    <div class="shop-flex shop-gap-md shop-items-center">
                        <div class="qty-stepper">
                            <button type="button" @click="decrementQty()">−</button>
                            <span x-text="qty"></span>
                            <button type="button" @click="incrementQty()">+</button>
                        </div>

                        <button type="submit" class="shop-btn shop-btn--primary" style="flex:1;" :disabled="!canAddToCart">
                            <x-icon name="cart" /> Ajouter au panier
                        </button>
                    </div>
                </form>

                <div class="pdp-trust-row">
                    <div class="pdp-trust-item"><x-icon name="truck" /> Livraison partout</div>
                    <div class="pdp-trust-item"><x-icon name="shield" /> Paiement sécurisé</div>
                    <div class="pdp-trust-item"><x-icon name="heart" /> Avis vérifiés</div>
                </div>

                <div class="pdp-accordion" style="margin-top: var(--shop-space-xl);">
                    <div class="pdp-accordion__item" x-data="accordion()" x-init="open = true">
                        <button type="button" class="pdp-accordion__trigger" :class="{ 'is-open': open }" @click="open = !open">
                            Description <x-icon name="chevron-down" />
                        </button>
                        <div class="pdp-accordion__panel" x-show="open" x-cloak>{{ $product->description }}</div>
                    </div>

                    @if ($product->variants->whereNotNull('size')->isNotEmpty())
                        <div class="pdp-accordion__item" x-data="accordion()">
                            <button type="button" class="pdp-accordion__trigger" :class="{ 'is-open': open }" @click="open = !open">
                                Guide des tailles <x-icon name="chevron-down" />
                            </button>
                            <div class="pdp-accordion__panel" x-show="open" x-cloak>
                                <table class="size-guide-table">
                                    <thead>
                                        <tr><th>Taille</th><th>XS</th><th>S</th><th>M</th><th>L</th><th>XL</th></tr>
                                    </thead>
                                    <tbody>
                                        <tr><td>Tour de poitrine (cm)</td><td>78</td><td>84</td><td>90</td><td>96</td><td>104</td></tr>
                                        <tr><td>Tour de taille (cm)</td><td>60</td><td>66</td><td>72</td><td>78</td><td>86</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <div class="pdp-accordion__item" x-data="accordion()">
                        <button type="button" class="pdp-accordion__trigger" :class="{ 'is-open': open }" @click="open = !open">
                            Avis clients ({{ $reviews->count() }}) <x-icon name="chevron-down" />
                        </button>
                        <div class="pdp-accordion__panel" x-show="open" x-cloak>
                            @forelse ($reviews as $review)
                                <div style="padding: var(--shop-space-md) 0; border-bottom: 1px solid var(--shop-color-border);">
                                    <div class="shop-flex shop-justify-between shop-items-center">
                                        <strong>{{ $review->author_name }}</strong>
                                        <x-star-rating :rating="$review->rating" />
                                    </div>
                                    @if ($review->isVerifiedPurchase())
                                        <span class="shop-review-card__badge"><x-icon name="check" /> Achat vérifié</span>
                                    @endif
                                    <p style="margin-top: var(--shop-space-sm);">{{ $review->comment }}</p>
                                </div>
                            @empty
                                <p>Aucun avis pour le moment.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($relatedProducts->isNotEmpty())
            <section style="margin-top: var(--shop-space-3xl);">
                <h2 class="shop-h2" style="margin-bottom: var(--shop-space-lg);" data-reveal>Vous aimerez aussi</h2>
                <div class="product-grid">
                    @foreach ($relatedProducts as $related)
                        <x-shop.product-card :product="$related" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-shop-layout>
