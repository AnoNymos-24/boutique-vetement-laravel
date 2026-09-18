<!DOCTYPE html>
<html lang="fr" data-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? $storeName }}</title>

        @if ($storeSetting?->logoUrl())
            <link rel="icon" href="{{ $storeSetting->logoUrl() }}">
        @endif

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/scss/shop.scss', 'resources/js/shop.js'])
    </head>
    <body class="shop">
        <div class="shop-announcement">Livraison partout, paiement via {{ \App\Models\StoreSetting::current()->enabledPaymentMethodsLabel() }}</div>

        <header class="shop-header" x-data="mobileNav()">
            <div class="shop-container shop-header__bar">
                <div class="shop-header__left">
                    <button type="button" class="shop-header__menu-btn" title="Menu" aria-label="Ouvrir le menu"
                            :aria-expanded="open" aria-controls="mobile-nav-menu" @click="open = true">
                        <x-icon name="menu" />
                    </button>

                    <a href="{{ route('shop.home') }}" class="shop-header__brand">
                        @if ($storeSetting?->logoUrl())
                            <img src="{{ $storeSetting->logoUrl() }}" alt="{{ $storeName }}" class="shop-header__logo">
                        @else
                            {{ $storeName }}
                        @endif
                    </a>
                </div>

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

            <nav class="shop-container shop-header__nav-row">
                <a href="{{ route('shop.catalog') }}" class="{{ request()->routeIs('shop.catalog') ? 'is-active' : '' }}">Boutique</a>
                @foreach ($shopNavCategories ?? [] as $navCategory)
                    <a href="{{ route('shop.catalog.category', $navCategory) }}" class="{{ request()->routeIs('shop.catalog.category') && request()->route('category')?->is($navCategory) ? 'is-active' : '' }}">{{ $navCategory->name }}</a>
                @endforeach
            </nav>

            <div x-show="open" x-cloak class="mobile-nav-sheet" @click.self="open = false" @keydown.escape.window="open = false"
                 x-transition.opacity.duration.250ms>
                <div id="mobile-nav-menu" class="mobile-nav-sheet__panel" x-trap.inert.noscroll="open"
                     role="dialog" aria-modal="true" aria-label="Menu"
                     x-transition:enter="shop-drawer-enter" x-transition:enter-start="shop-drawer-enter-start" x-transition:enter-end=""
                     x-transition:leave="shop-drawer-leave" x-transition:leave-start="" x-transition:leave-end="shop-drawer-leave-end">
                    <div class="mobile-nav-sheet__header">
                        <span class="shop-header__brand">{{ $storeName }}</span>
                        <button type="button" @click="open = false" class="shop-btn shop-btn--icon shop-btn--ghost" aria-label="Fermer le menu">
                            <x-icon name="x" />
                        </button>
                    </div>

                    <form method="GET" action="{{ route('shop.catalog') }}" class="mobile-nav-sheet__search">
                        <x-icon name="search" />
                        <input type="search" name="search" placeholder="Rechercher un article…" value="{{ request('search') }}">
                    </form>

                    <nav class="mobile-nav-sheet__links">
                        <a href="{{ route('shop.catalog') }}" @click="open = false" class="{{ request()->routeIs('shop.catalog') ? 'is-active' : '' }}">Boutique</a>
                        @foreach ($shopNavCategories ?? [] as $navCategory)
                            <a href="{{ route('shop.catalog.category', $navCategory) }}" @click="open = false" class="{{ request()->routeIs('shop.catalog.category') && request()->route('category')?->is($navCategory) ? 'is-active' : '' }}">{{ $navCategory->name }}</a>
                        @endforeach
                    </nav>
                </div>
            </div>
        </header>

        <main>
            @if (session('status') || session('error'))
                <div class="shop-container" style="padding-top: var(--shop-space-lg);">
                    @if (session('status'))
                        <div class="shop-status-banner" data-reveal>{{ session('status') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="shop-status-banner shop-status-banner--error" data-reveal>{{ session('error') }}</div>
                    @endif
                </div>
            @endif

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
                                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" title="{{ $storeSetting->socialLinkLabel($network) }}">
                                        <x-icon :name="$network" />
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        @if ($storeSetting?->address)
                            <p class="shop-text-muted" style="font-size:0.8125rem; margin-top: var(--shop-space-md); display:flex; align-items:flex-start; gap:6px;">
                                <x-icon name="map-pin" style="width:14px;height:14px;flex-shrink:0;margin-top:2px;" />
                                {{ $storeSetting->address }}
                            </p>
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
                            <li><a href="{{ route('shop.contact') }}">Nous contacter</a></li>
                            <li><a href="{{ route('shop.terms') }}">Conditions générales</a></li>
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
                            @forelse (\App\Models\StoreSetting::current()->enabledPaymentMethods() as $method)
                                <li class="shop-text-muted">{{ $method['label'] }}</li>
                            @empty
                                <li class="shop-text-muted">Nous contacter</li>
                            @endforelse
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
