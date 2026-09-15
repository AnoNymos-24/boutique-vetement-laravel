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

        <div class="pdp-layout" x-data="variantSelector(@js($variantsForJs))">
            <div class="pdp-gallery" x-data="{ mainImage: '{{ $product->images->first()?->url() }}' }" data-reveal>
                <div class="pdp-gallery__main">
                    @if ($product->images->first())
                        <img :src="mainImage" alt="{{ $product->name }}">
                    @endif
                </div>
                @if ($product->images->count() > 1)
                    <div class="pdp-gallery__thumbs">
                        @foreach ($product->images as $index => $image)
                            <button type="button" class="pdp-gallery__thumb"
                                    :class="{ 'is-active': mainImage === '{{ $image->url() }}' }"
                                    @click="mainImage = '{{ $image->url() }}'">
                                <img src="{{ $image->url() }}" alt="">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="pdp-buybox" data-reveal>
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
                        <span class="pdp-buybox__price--compare">{{ number_format($product->compare_at_price / 100, 3, ',', ' ') }} DT</span>
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
                        <div class="pdp-accordion__panel" x-show="open" x-transition.opacity.duration.200ms x-cloak>{{ $product->description }}</div>
                    </div>

                    @if ($product->variants->whereNotNull('size')->isNotEmpty())
                        <div class="pdp-accordion__item" x-data="accordion()">
                            <button type="button" class="pdp-accordion__trigger" :class="{ 'is-open': open }" @click="open = !open">
                                Guide des tailles <x-icon name="chevron-down" />
                            </button>
                            <div class="pdp-accordion__panel" x-show="open" x-transition.opacity.duration.200ms x-cloak>
                                <div style="overflow-x: auto;">
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
                        </div>
                    @endif

                    <div class="pdp-accordion__item" x-data="accordion()">
                        <button type="button" class="pdp-accordion__trigger" :class="{ 'is-open': open }" @click="open = !open">
                            Avis clients ({{ $reviews->count() }}) <x-icon name="chevron-down" />
                        </button>
                        <div class="pdp-accordion__panel" x-show="open" x-transition.opacity.duration.200ms x-cloak>
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

                            <div style="margin-top: var(--shop-space-lg); padding-top: var(--shop-space-lg); border-top: 1px solid var(--shop-color-border);">
                                @if (! $customer)
                                    <p class="shop-text-muted" style="font-size:0.875rem; margin-bottom: var(--shop-space-sm);">
                                        Connectez-vous pour laisser un avis sur cet article.
                                    </p>
                                    <a href="{{ route('customer.login', ['redirect_to' => url()->current()]) }}" class="shop-btn shop-btn--secondary shop-btn--sm">
                                        Se connecter pour laisser un avis
                                    </a>
                                @elseif ($alreadyReviewed)
                                    <p class="shop-text-muted" style="font-size:0.875rem;">
                                        <x-icon name="check" style="width:16px;height:16px;" /> Vous avez déjà laissé un avis sur cet article.
                                    </p>
                                @elseif (! $canReview)
                                    <p class="shop-text-muted" style="font-size:0.875rem;">
                                        Vous ne pouvez commenter que les articles que vous avez déjà achetés.
                                    </p>
                                @else
                                    <p class="shop-label" style="margin-bottom: var(--shop-space-md);">Laisser un avis sur cet article</p>
                                    <form method="POST" action="{{ route('customer.reviews.store') }}">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                                        <div class="shop-field" x-data="{ rating: 5 }">
                                            <label class="shop-label">Note</label>
                                            <input type="hidden" name="rating" x-model="rating">
                                            <div class="shop-flex shop-gap-sm">
                                                <template x-for="n in 5" :key="n">
                                                    <button type="button" @click="rating = n" style="cursor:pointer;">
                                                        <x-icon name="star" x-bind:style="'width:22px;height:22px;' + (n <= rating ? 'color:var(--shop-color-accent);fill:var(--shop-color-accent);' : 'color:var(--shop-color-border);')" />
                                                    </button>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="shop-field">
                                            <label class="shop-label" for="pdp-comment">Votre avis</label>
                                            <textarea id="pdp-comment" name="comment" class="shop-textarea" rows="3" required></textarea>
                                        </div>

                                        <button type="submit" class="shop-btn shop-btn--primary shop-btn--sm">Envoyer l'avis</button>
                                    </form>
                                @endif
                            </div>
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
