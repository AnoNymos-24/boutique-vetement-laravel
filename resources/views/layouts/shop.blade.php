<!DOCTYPE html>
<html lang="fr" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? $storeName }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Inter:wght@400;500;600;700&family=Fira+Code:wght@500;600&display=swap" rel="stylesheet">

        @vite(['resources/scss/shop.scss', 'resources/js/shop.js'])
    </head>
    <body class="shop">
        <header class="shop-header">
            <div class="shop-container shop-header__bar">
                <a href="{{ route('shop.home') }}" class="shop-header__brand">
                    @if ($storeSetting?->logoUrl())
                        <img src="{{ $storeSetting->logoUrl() }}" alt="{{ $storeName }}" class="shop-header__logo">
                    @else
                        {{ $storeName }}
                    @endif
                </a>

                <nav class="shop-header__nav">
                    <a href="{{ route('shop.catalog') }}">Boutique</a>
                    @foreach ($shopNavCategories ?? [] as $navCategory)
                        <a href="{{ route('shop.catalog.category', $navCategory) }}">{{ $navCategory->name }}</a>
                    @endforeach
                </nav>

                <form method="GET" action="{{ route('shop.catalog') }}" class="shop-header__search">
                    <x-icon name="search" />
                    <input type="search" name="search" placeholder="Rechercher un article…" value="{{ request('search') }}">
                </form>

                <div class="shop-header__actions">
                    @auth('customer')
                        <a href="{{ route('customer.account') }}" class="shop-header__icon-btn" title="Mon compte">
                            <x-icon name="users" />
                        </a>
                    @else
                        <button type="button" class="shop-header__icon-btn" title="Connexion" x-data
                                @click="$dispatch('open-modal', 'customer-login')">
                            <x-icon name="users" />
                        </button>
                    @endauth

                    <a href="{{ route('shop.cart') }}" class="shop-header__icon-btn" title="Panier">
                        <x-icon name="cart" />
                        @if (($shopCartCount ?? 0) > 0)
                            <span class="shop-header__badge">{{ $shopCartCount }}</span>
                        @endif
                    </a>
                </div>
            </div>
        </header>

        <main>
            <div class="shop-container" style="padding-top: var(--shop-space-lg);">
                @if (session('status'))
                    <div class="shop-status-banner" data-reveal>{{ session('status') }}</div>
                @endif
                @if (session('error'))
                    <div class="shop-status-banner shop-status-banner--error" data-reveal>{{ session('error') }}</div>
                @endif
            </div>

            {{ $slot }}
        </main>

        <footer class="shop-footer">
            <div class="shop-container">
                <div class="shop-footer__grid">
                    <div>
                        @if ($storeSetting?->logoUrl())
                            <img src="{{ $storeSetting->logoUrl() }}" alt="{{ $storeName }}" class="shop-footer__logo">
                        @else
                            <div class="shop-footer__brand">{{ $storeName }}</div>
                        @endif
                        <p class="shop-text-muted" style="font-size:0.875rem; max-width:32ch; margin-top: var(--shop-space-sm);">
                            {{ $storeSetting?->description ?: 'Vêtements et accessoires sélectionnés avec soin, livrés où que vous soyez.' }}
                        </p>

                        @if ($storeSetting && $storeSetting->socialLinks() !== [])
                            <div class="shop-footer__socials">
                                @foreach ($storeSetting->socialLinks() as $network => $url)
                                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" title="{{ ucfirst($network) }}">
                                        <x-icon :name="$network" />
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div>
                        <h4>Boutique</h4>
                        <ul>
                            <li><a href="{{ route('shop.catalog') }}">Tout le catalogue</a></li>
                            @foreach ($shopNavCategories ?? [] as $navCategory)
                                <li><a href="{{ route('shop.catalog.category', $navCategory) }}">{{ $navCategory->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <h4>Aide</h4>
                        <ul>
                            <li><a href="{{ route('shop.cart') }}">Mon panier</a></li>
                            <li>
                                @auth('customer')
                                    <a href="{{ route('customer.account') }}">Mon compte</a>
                                @else
                                    <button type="button" x-data @click="$dispatch('open-modal', 'customer-login')">Mon compte</button>
                                @endauth
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h4>Paiement</h4>
                        <ul>
                            <li class="shop-text-muted">Orange Money</li>
                            <li class="shop-text-muted">Wave</li>
                        </ul>
                    </div>
                </div>

                <div class="shop-footer__bottom">
                    © {{ now()->year }} {{ $storeName }}. Tous droits réservés.
                </div>
            </div>
        </footer>

        @include('shop.partials.login-modal')
    </body>
</html>
