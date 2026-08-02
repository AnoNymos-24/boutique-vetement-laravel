@php
    $filtersActive = request()->anyFilled(['size', 'color', 'price_max', 'in_stock', 'search']);
@endphp

<x-shop-layout>
    <div class="shop-container" style="padding: var(--shop-space-lg) var(--shop-space-lg) var(--shop-space-3xl);">
        <div style="margin-bottom: var(--shop-space-lg);" data-reveal>
            <p class="shop-eyebrow">Boutique</p>
            <h1 class="shop-h1" style="font-size: 2rem;">{{ $activeCategory?->name ?? 'Tout le catalogue' }}</h1>
            <p class="shop-text-muted">{{ $products->total() }} article(s)</p>
        </div>

        <div x-data="mobileFilters()">
            <button type="button" class="shop-btn shop-btn--secondary mobile-filter-trigger" @click="open = true" style="margin-bottom: var(--shop-space-lg);">
                <x-icon name="grid" /> Filtres
            </button>

            <div class="catalog-layout">
                <aside class="filter-sidebar">
                    @include('shop.partials.catalog-filters')
                </aside>

                <div x-show="open" x-cloak class="mobile-filter-sheet" @click.self="open = false">
                    <div class="mobile-filter-sheet__panel">
                        <div class="shop-flex shop-justify-between shop-items-center" style="margin-bottom: var(--shop-space-md);">
                            <h3 class="shop-h3">Filtres</h3>
                            <button type="button" @click="open = false" class="shop-btn shop-btn--icon shop-btn--ghost"><x-icon name="x" /></button>
                        </div>
                        @include('shop.partials.catalog-filters')
                    </div>
                </div>

                <div>
                    <div class="catalog-toolbar">
                        @if ($filtersActive)
                            <a href="{{ $activeCategory ? route('shop.catalog.category', $activeCategory) : route('shop.catalog') }}" class="shop-btn shop-btn--ghost shop-btn--sm">
                                <x-icon name="x" /> Réinitialiser les filtres
                            </a>
                        @else
                            <span></span>
                        @endif

                        <form method="GET" class="shop-flex shop-gap-sm shop-items-center">
                            @foreach (request()->except('sort', 'page') as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <label for="sort" class="shop-text-muted" style="font-size:0.8125rem;">Trier :</label>
                            <select id="sort" name="sort" class="shop-select" style="width:auto;" onchange="this.form.submit()">
                                <option value="nouveautes" @selected(request('sort', 'nouveautes') === 'nouveautes')>Nouveautés</option>
                                <option value="prix_asc" @selected(request('sort') === 'prix_asc')>Prix croissant</option>
                                <option value="prix_desc" @selected(request('sort') === 'prix_desc')>Prix décroissant</option>
                            </select>
                        </form>
                    </div>

                    @if ($products->isEmpty())
                        <div class="empty-state" data-reveal>
                            <x-icon name="package" />
                            <p class="shop-h3">Aucun article ne correspond à ces filtres.</p>
                            <p class="shop-text-muted">Essayez d'élargir votre recherche.</p>
                        </div>
                    @else
                        <div class="product-grid">
                            @foreach ($products as $product)
                                <x-shop.product-card :product="$product" />
                            @endforeach
                        </div>

                        {{ $products->onEachSide(1)->links('pagination.shop') }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-shop-layout>
