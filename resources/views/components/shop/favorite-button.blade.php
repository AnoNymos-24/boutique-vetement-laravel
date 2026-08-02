@props(['product', 'active' => null, 'inline' => false])

@auth('customer')
    @php
        $isFavorited = $active !== null
            ? filter_var($active, FILTER_VALIDATE_BOOLEAN)
            : auth('customer')->user()->wishlists()->where('product_id', $product->id)->exists();
    @endphp

    <form method="POST" action="{{ route('customer.wishlist.toggle', $product) }}" style="{{ $inline ? 'display:inline-block;' : '' }}">
        @csrf
        <button type="submit" class="favorite-btn {{ $isFavorited ? 'is-active' : '' }} {{ $inline ? 'favorite-btn--inline' : '' }}"
                title="{{ $isFavorited ? 'Retirer des favoris' : 'Ajouter aux favoris' }}">
            <x-icon name="heart" />
        </button>
    </form>
@endauth
