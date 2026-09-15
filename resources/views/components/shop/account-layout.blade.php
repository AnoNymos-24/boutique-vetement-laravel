@props(['active'])

@php
    $navItems = [
        'dashboard' => ['label' => 'Tableau de bord', 'icon' => 'grid', 'route' => route('customer.account')],
        'orders' => ['label' => 'Mes commandes', 'icon' => 'box', 'route' => route('customer.account.orders')],
        'addresses' => ['label' => 'Mes adresses', 'icon' => 'map-pin', 'route' => route('customer.addresses')],
        'wishlist' => ['label' => 'Mes favoris', 'icon' => 'heart', 'route' => route('customer.wishlist')],
        'reviews' => ['label' => 'Mes avis', 'icon' => 'star', 'route' => route('customer.reviews')],
        'complaints' => ['label' => 'Mes réclamations', 'icon' => 'flag', 'route' => route('customer.complaints')],
    ];
@endphp

<div style="margin-bottom: var(--shop-space-xl);" data-reveal>
    <p class="shop-eyebrow">Mon compte</p>
    <h1 class="shop-h1" style="font-size: 2rem;">Bonjour {{ auth('customer')->user()->name }}</h1>
</div>

<div class="account-layout" data-reveal>
    <nav class="account-nav shop-card">
        @foreach ($navItems as $key => $item)
            <a href="{{ $item['route'] }}" class="account-nav__item {{ $active === $key ? 'is-active' : '' }}">
                <span class="account-nav__indicator"></span>
                <x-icon :name="$item['icon']" /> {{ $item['label'] }}
            </a>
        @endforeach

        <form method="POST" action="{{ route('customer.logout') }}" style="margin-top: var(--shop-space-sm); padding-top: var(--shop-space-sm); border-top: 1px solid var(--shop-color-border);">
            @csrf
            <button type="submit" class="account-nav__item account-nav__item--danger">
                <x-icon name="logout" /> Se déconnecter
            </button>
        </form>
    </nav>

    <div>
        {{ $slot }}
    </div>
</div>
