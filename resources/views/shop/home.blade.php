<x-shop-layout>
    <section class="shop-container" style="padding: var(--shop-space-2xl) var(--shop-space-lg) var(--shop-space-3xl);">
        <div style="display:grid; grid-template-columns: 1.1fr 0.9fr; gap: var(--shop-space-2xl); align-items:center;" data-reveal>
            <div>
                <p class="shop-eyebrow">Nouvelle collection</p>
                <h1 class="shop-h1" style="margin-top: var(--shop-space-sm);">Des pièces choisies pour vous accompagner, saison après saison.</h1>
                <p class="shop-text-muted" style="font-size:1.0625rem; margin-top: var(--shop-space-md); max-width: 42ch;">
                    Vêtements et accessoires sélectionnés avec soin, en tailles et couleurs variées, livrés où que vous soyez via Orange Money ou Wave.
                </p>
                <a href="{{ route('shop.catalog') }}" class="shop-btn shop-btn--primary" style="margin-top: var(--shop-space-xl);">
                    Découvrir la boutique <x-icon name="arrow-right" />
                </a>
            </div>

            @if ($newArrivals->first()?->images->first())
                <div style="aspect-ratio: 4/5; border-radius: var(--shop-radius-lg); overflow:hidden; box-shadow: var(--shop-shadow-xl);">
                    <img src="{{ $newArrivals->first()->images->first()->url() }}" alt="" style="width:100%; height:100%; object-fit:cover;">
                </div>
            @endif
        </div>
    </section>

    @if ($categories->isNotEmpty())
        <section class="shop-container" style="padding-bottom: var(--shop-space-3xl);">
            <h2 class="shop-h2" data-reveal style="margin-bottom: var(--shop-space-lg);">Catégories</h2>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: var(--shop-space-md);">
                @foreach ($categories as $category)
                    <a href="{{ route('shop.catalog.category', $category) }}" data-reveal
                       style="position:relative; aspect-ratio: 1; border-radius: var(--shop-radius-lg); overflow:hidden; display:flex; align-items:flex-end; background: var(--shop-color-muted);">
                        @if ($category->cover)
                            <img src="{{ $category->cover }}" alt="" style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover;">
                        @endif
                        <span style="position:relative; z-index:1; width:100%; padding: var(--shop-space-md); background: linear-gradient(to top, rgba(15,23,42,.55), transparent); color:#fff; font-weight:600;">
                            {{ $category->name }}
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if ($newArrivals->isNotEmpty())
        <section class="shop-container" style="padding-bottom: var(--shop-space-3xl);">
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
        <section class="shop-container" style="padding-bottom: var(--shop-space-3xl);">
            <h2 class="shop-h2" data-reveal style="margin-bottom: var(--shop-space-lg);">Elles nous font confiance</h2>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: var(--shop-space-lg);">
                @foreach ($reviews as $review)
                    <div class="shop-review-card" data-reveal>
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

    <section style="background: var(--shop-color-primary); color:#fff; padding: var(--shop-space-2xl) 0; margin-top: var(--shop-space-xl);">
        <div class="shop-container" style="text-align:center;" data-reveal>
            <h2 class="shop-h2" style="color:#fff;">Prête à renouveler votre garde-robe ?</h2>
            <p style="opacity:.85; margin-top: var(--shop-space-sm);">Livraison partout, paiement Orange Money ou Wave.</p>
            <a href="{{ route('shop.catalog') }}" class="shop-btn shop-btn--accent" style="margin-top: var(--shop-space-lg);">
                Voir tout le catalogue
            </a>
        </div>
    </section>
</x-shop-layout>
