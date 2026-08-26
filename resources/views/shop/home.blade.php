<x-shop-layout>
    @if ($banners->isNotEmpty())
        <section class="shop-banner-hero" x-data="bannerCarousel({{ $banners->count() }})" data-reveal>
            <div class="shop-banner-hero__viewport">
                @foreach ($banners as $index => $banner)
                    <div class="shop-banner-hero__slide" x-show="active === {{ $index }}" x-cloak
                         x-transition.opacity.duration.500ms
                         style="background-image: url('{{ $banner->imageUrl() }}');">
                        <div class="shop-banner-hero__overlay"></div>
                        <div class="shop-container">
                            <div class="shop-banner-hero__content">
                                @if ($banner->promo_text)
                                    <span class="shop-banner-hero__promo">{{ $banner->promo_text }}</span>
                                @endif
                                <h1 class="shop-banner-hero__title">{{ $banner->title }}</h1>
                                @if ($banner->subtitle)
                                    <p class="shop-banner-hero__subtitle">{{ $banner->subtitle }}</p>
                                @endif
                                @if ($banner->button_text)
                                    <a href="{{ $banner->button_link ?: route('shop.catalog') }}" class="shop-btn shop-btn--primary shop-banner-hero__cta">
                                        {{ $banner->button_text }} <x-icon name="arrow-right" />
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($banners->count() > 1)
                <div class="shop-banner-hero__dots">
                    @foreach ($banners as $index => $banner)
                        <button type="button" class="shop-banner-hero__dot" :class="{ 'is-active': active === {{ $index }} }"
                                @click="go({{ $index }})" aria-label="Bannière {{ $index + 1 }} sur {{ $banners->count() }}"></button>
                    @endforeach
                </div>
            @endif
        </section>
    @else
        <section class="shop-container shop-hero">
            <div class="shop-hero__grid" data-reveal>
                <div>
                    <p class="shop-eyebrow">Nouvelle collection</p>
                    <h1 class="shop-hero__title">Des pièces choisies pour vous accompagner, saison après saison.</h1>
                    <p class="shop-text-muted shop-hero__lede">
                        Vêtements et accessoires sélectionnés avec soin, en tailles et couleurs variées, livrés où que vous soyez via Orange Money ou Wave.
                    </p>
                    <a href="{{ route('shop.catalog') }}" class="shop-btn shop-btn--primary shop-hero__cta">
                        Découvrir la boutique <x-icon name="arrow-right" />
                    </a>
                </div>

                @if ($newArrivals->first()?->images->first())
                    <div class="shop-hero__media">
                        <div class="shop-hero__media-frame">
                            <img src="{{ $newArrivals->first()->images->first()->url() }}" alt="">
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if ($categories->isNotEmpty())
        <section class="shop-container shop-section">
            <h2 class="shop-h2" data-reveal style="margin-bottom: var(--shop-space-lg);">Catégories</h2>
            <div class="shop-category-grid">
                @foreach ($categories as $category)
                    <a href="{{ route('shop.catalog.category', $category) }}" class="shop-category-tile">
                        @if ($category->cover)
                            <img src="{{ $category->cover }}" alt="" class="shop-category-tile__image">
                        @endif
                        <span class="shop-category-tile__label">
                            <span class="shop-category-tile__name">{{ $category->name }}</span>
                            <span class="shop-category-tile__cta">Découvrir <x-icon name="arrow-right" /></span>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if ($newArrivals->isNotEmpty())
        <section class="shop-container shop-section">
            <div class="shop-flex shop-justify-between shop-items-center" style="margin-bottom: var(--shop-space-lg);" data-reveal>
                <h2 class="shop-h2">Nouveautés</h2>
                <a href="{{ route('shop.catalog') }}" class="shop-btn shop-btn--secondary shop-btn--sm">Tout voir</a>
            </div>
            <div class="product-grid">
                @foreach ($newArrivals as $product)
                    <x-shop.product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif

    @if ($reviews->isNotEmpty())
        <section class="shop-container shop-section">
            <h2 class="shop-h2" data-reveal style="margin-bottom: var(--shop-space-lg);">Elles nous font confiance</h2>
            <div class="shop-review-grid">
                @foreach ($reviews as $review)
                    <div class="shop-review-card shop-card">
                        <div class="shop-review-card__header">
                            <span class="shop-review-card__author">{{ $review->author_name }}</span>
                            <x-star-rating :rating="$review->rating" />
                        </div>
                        <p class="shop-review-card__comment">« {{ Str::limit($review->comment, 140) }} »</p>
                        <p class="shop-review-card__date">{{ $review->product->name }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <section class="shop-cta-banner" style="--shop-cta-word: '{{ Str::upper($storeName ?? 'INA') }}';">
        <div class="shop-container shop-cta-banner__content" data-reveal>
            <h2 class="shop-h2" style="color:#fff;">Prête à renouveler votre garde-robe ?</h2>
            <p class="shop-cta-banner__lede">Livraison partout, paiement Orange Money ou Wave.</p>
            <a href="{{ route('shop.catalog') }}" class="shop-btn shop-btn--accent" style="margin-top: var(--shop-space-lg);">
                Voir tout le catalogue
            </a>
        </div>
    </section>
</x-shop-layout>
