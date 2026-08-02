<!DOCTYPE html>
<html lang="fr" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Tableau de bord' }} — {{ $storeName }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@500;600;700&family=Fira+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    </head>
    <body>
        <div class="app-shell" x-data="sidebar()" :class="{ 'is-collapsed': collapsed, 'sidebar-open': mobileOpen }">
            <div class="sidebar-backdrop" @click="mobileOpen = false"></div>

            <aside class="sidebar">
                <div class="sidebar-brand">
                    <div class="sidebar-brand__mark">BB</div>
                    <span class="sidebar-brand__name">{{ $storeName }}</span>
                </div>

                <nav class="sidebar-nav">
                    @include('layouts.navigation-items')
                </nav>

                <button type="button" class="sidebar-toggle" @click="toggleCollapsed()" title="Replier / déplier le menu">
                    <x-icon name="chevron-left" x-show="!collapsed" x-cloak />
                    <x-icon name="chevron-right" x-show="collapsed" x-cloak />
                </button>
            </aside>

            <div class="app-main">
                <header class="topbar">
                    <button type="button" class="topbar__menu-btn" @click="toggleMobile()">
                        <x-icon name="menu" />
                    </button>

                    <div class="topbar__search search-input">
                        <x-icon name="search" />
                        <input type="search" class="form-input" placeholder="Rechercher un produit, une commande…">
                    </div>

                    <div class="topbar__actions">
                        <button type="button" class="icon-btn" title="Notifications">
                            <x-icon name="bell" />
                            @if (($outOfStockCount ?? 0) > 0)
                                <span class="icon-btn__dot"></span>
                            @endif
                        </button>

                        <x-dropdown align="right">
                            <x-slot name="trigger">
                                <button type="button" class="user-menu-trigger">
                                    <div class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                                    <div class="user-menu-trigger__info">
                                        <div class="user-menu-trigger__name">{{ auth()->user()->name }}</div>
                                        <div class="user-menu-trigger__role">{{ auth()->user()->role === 'admin' ? 'Administrateur' : 'Vendeur' }}</div>
                                    </div>
                                    <x-icon name="chevron-down" class="icon text-muted" />
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">
                                    <x-icon name="settings" /> Mon profil
                                </x-dropdown-link>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                                        <x-icon name="logout" /> Déconnexion
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </header>

                <main class="app-content">
                    @if (session('status'))
                        <div class="status-banner" style="margin-bottom: var(--space-xl);" data-reveal>
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="status-banner status-banner--error" style="margin-bottom: var(--space-xl);" data-reveal>
                            {{ session('error') }}
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
