@props(['product', 'favorited' => null])

@php
    $inStock = $product->relationLoaded('variants') ? $product->variants->sum('stock_quantity') > 0 : true;
@endphp

<div class="product-card-wrap">
    <x-shop.favorite-button :product="$product" :active="$favorited" />

    <a href="{{ route('shop.product', $product) }}" class="product-card">
        <div class="product-card__image-wrap">
            @if ($product->images->first())
                <img src="{{ $product->images->first()->url() }}" alt="{{ $product->name }}" class="product-card__image product-card__image--primary">
                @if ($product->images->get(1))
                    <img src="{{ $product->images->get(1)->url() }}" alt="" class="product-card__image product-card__image--secondary">
                @endif
            @else
                <div class="product-card__image product-card__image--empty">
                    <x-icon name="shirt" />
                </div>
            @endif

            @if (! $inStock)
                <span class="product-card__badge product-card__badge--rupture">Épuisé</span>
            @elseif ($product->compare_at_price)
                <span class="product-card__badge">Promo</span>
            @endif
        </div>

        <div class="product-card__body">
            <div class="product-card__category">{{ $product->category->name }}</div>
            <div class="product-card__name">{{ $product->name }}</div>
        </div>

         <div class="product-card__prices">
            <span class="product-card__price">{{ $product->priceFormatted() }}</span>
            @if ($product->compare_at_price)
                <span class="product-card__price--compare">{{ \App\Models\StoreSetting::current()->formatMoney($product->compare_at_price) }}</span>
            @endif
        </div> 
    </a>
</div>
