@php
    $catalogBaseRoute = $activeCategory ? route('shop.catalog.category', $activeCategory) : route('shop.catalog');
@endphp

<div class="filter-group">
    <div class="filter-group__title">Catégories</div>
    <a href="{{ route('shop.catalog') }}" class="filter-option" style="font-weight: {{ ! $activeCategory ? 700 : 400 }};">
        Tout voir
    </a>
    @foreach ($categories as $cat)
        <a href="{{ route('shop.catalog.category', $cat) }}" class="filter-option" style="font-weight: {{ $activeCategory?->id === $cat->id ? 700 : 400 }};">
            {{ $cat->name }}
        </a>
    @endforeach
</div>

@if ($sizes->isNotEmpty())
    <div class="filter-group">
        <div class="filter-group__title">Taille</div>
        <div class="filter-swatch-row">
            @foreach ($sizes as $size)
                <a href="{{ request('size') === $size ? $catalogBaseRoute.'?'.http_build_query(request()->except('size', 'page')) : $catalogBaseRoute.'?'.http_build_query(array_merge(request()->except('page'), ['size' => $size])) }}"
                   class="filter-swatch {{ request('size') === $size ? 'is-active' : '' }}">
                    {{ $size }}
                </a>
            @endforeach
        </div>
    </div>
@endif

@if ($colors->isNotEmpty())
    <div class="filter-group">
        <div class="filter-group__title">Couleur</div>
        <div class="filter-swatch-row">
            @foreach ($colors as $color)
                <a href="{{ request('color') === $color ? $catalogBaseRoute.'?'.http_build_query(request()->except('color', 'page')) : $catalogBaseRoute.'?'.http_build_query(array_merge(request()->except('page'), ['color' => $color])) }}"
                   class="filter-swatch {{ request('color') === $color ? 'is-active' : '' }}">
                    {{ $color }}
                </a>
            @endforeach
        </div>
    </div>
@endif

<div class="filter-group">
    <div class="filter-group__title">Prix maximum</div>
    <form method="GET" action="{{ $catalogBaseRoute }}">
        @foreach (request()->except('price_max', 'page') as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
        <input type="number" name="price_max" class="shop-input" placeholder="Ex. 150" value="{{ request('price_max') }}" min="0" onchange="this.form.submit()">
    </form>
</div>

<div class="filter-group" style="border-bottom: none;">
    <label class="filter-option">
        <input type="checkbox" onchange="window.location.href = this.checked
            ? '{{ $catalogBaseRoute }}?{{ http_build_query(array_merge(request()->except('page'), ['in_stock' => 1])) }}'
            : '{{ $catalogBaseRoute }}?{{ http_build_query(request()->except('in_stock', 'page')) }}'"
            {{ request()->boolean('in_stock') ? 'checked' : '' }}>
        Disponible uniquement
    </label>
</div>
