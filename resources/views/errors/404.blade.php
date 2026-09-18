@if (request()->is('admin/*'))
    <x-app-layout>
        <div class="page-header" data-reveal>
            <div class="page-header__title">
                <h1 class="h1">Page introuvable</h1>
                <p class="text-muted text-sm">Erreur 404</p>
            </div>
        </div>

        <div class="card" data-reveal style="max-width:480px; margin: var(--space-3xl) auto; text-align:center; padding: var(--space-2xl);">
            <x-icon name="warning" style="width:48px; height:48px; color: var(--color-warning, currentColor); margin: 0 auto var(--space-md);" />
            <p class="h2" style="margin-bottom: var(--space-xs);">Cette page n'existe pas</p>
            <p class="text-muted" style="margin-bottom: var(--space-lg);">Le lien est peut-être obsolète, ou la ressource a été supprimée entre-temps.</p>
            <a href="{{ route('dashboard') }}" class="btn btn-primary"><x-icon name="arrow-left" /> Retour au tableau de bord</a>
        </div>
    </x-app-layout>
@else
    <x-shop-layout>
        <div class="shop-container">
            <div class="empty-state" data-reveal style="padding: var(--shop-space-3xl) var(--shop-space-lg);">
                <x-icon name="shirt" />
                <p class="shop-h3">Cette page n'existe pas</p>
                <p class="shop-text-muted" style="margin-bottom: var(--shop-space-lg);">Le lien est peut-être obsolète, ou l'article a été retiré de la boutique.</p>
                <div style="display:flex; gap: var(--shop-space-sm); justify-content:center; flex-wrap:wrap;">
                    <a href="{{ route('shop.home') }}" class="shop-btn shop-btn--primary">Retour à l'accueil</a>
                    <a href="{{ route('shop.catalog') }}" class="shop-btn shop-btn--ghost">Voir la boutique</a>
                </div>
            </div>
        </div>
    </x-shop-layout>
@endif
