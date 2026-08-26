@php
    $items = [
        null => [
            ['route' => 'dashboard', 'icon' => 'grid', 'label' => 'Tableau de bord'],
        ],
        'Catalogue' => [
            ['route' => 'products.index', 'icon' => 'shirt', 'label' => 'Produits'],
            ['route' => 'stock', 'icon' => 'box', 'label' => 'Stock', 'badge' => $outOfStockCount ?? null],
        ],
        'Ventes' => [
            ['route' => 'orders', 'icon' => 'cart', 'label' => 'Commandes'],
            ['route' => 'pos', 'icon' => 'cash', 'label' => 'Vente manuelle'],
            ['route' => 'finance', 'icon' => 'chart', 'label' => 'Finance'],
        ],
        'Relation client' => [
            ['route' => 'clients', 'icon' => 'users', 'label' => 'Clients'],
            ['route' => 'reviews', 'icon' => 'star', 'label' => 'Avis'],
            ['route' => 'marketing', 'icon' => 'megaphone', 'label' => 'Marketing'],
            ['route' => 'banners.index', 'icon' => 'image', 'label' => 'Bannières'],
        ],
    ];

    $settingsChildren = [
        ['route' => 'settings.identity.edit', 'label' => 'Identité de la boutique'],
        ['route' => 'settings.categories.index', 'label' => 'Catégories'],
        ['route' => 'settings.taxonomies.index', 'label' => 'Étiquettes & tags'],
    ];
    $settingsActive = request()->routeIs('settings.*');
@endphp

@foreach ($items as $section => $links)
    @if ($section)
        <div class="sidebar-nav__section">{{ $section }}</div>
    @endif
    @foreach ($links as $link)
        <a href="{{ route($link['route']) }}" class="sidebar-link {{ request()->routeIs($link['route'] . '*') ? 'is-active' : '' }}">
            <x-icon :name="$link['icon']" />
            <span>{{ $link['label'] }}</span>
            @if (!empty($link['badge']))
                <span class="sidebar-link__badge">{{ $link['badge'] }}</span>
            @endif
        </a>
    @endforeach
@endforeach

<div class="sidebar-nav__section">Administration</div>
<div class="sidebar-group" x-data="{ open: @js($settingsActive) }">
    <button type="button" class="sidebar-link sidebar-group__trigger {{ $settingsActive ? 'is-active' : '' }}" @click="open = !open">
        <x-icon name="settings" />
        <span>Paramètres</span>
        <x-icon name="chevron-down" class="icon sidebar-group__chevron" x-bind:class="{ 'is-rotated': open }" />
    </button>

    <div class="sidebar-submenu-wrapper" :class="{ 'is-open': open }">
        <div class="sidebar-submenu">
            @foreach ($settingsChildren as $child)
                <a href="{{ route($child['route']) }}" class="sidebar-sublink {{ request()->routeIs($child['route']) ? 'is-active' : '' }}">
                    {{ $child['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>
