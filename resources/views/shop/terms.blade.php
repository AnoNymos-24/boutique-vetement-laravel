<x-shop-layout>
    <div class="shop-container" style="padding: var(--shop-space-2xl) var(--shop-space-lg) var(--shop-space-3xl); max-width: 800px;">
        <div style="margin-bottom: var(--shop-space-xl);" data-reveal>
            <p class="shop-eyebrow">{{ $setting->name }}</p>
            <h1 class="shop-h1" style="font-size: clamp(1.5rem, 4vw, 2.25rem);">Conditions générales d'utilisation et de vente</h1>
            @if ($setting->terms_updated_at)
                <p class="shop-text-muted" style="font-size:0.8125rem; margin-top: var(--shop-space-sm);">
                    Dernière mise à jour : {{ $setting->terms_updated_at->translatedFormat('d F Y') }}
                </p>
            @endif
        </div>

        @if ($setting->terms_content)
            <div class="shop-prose" data-reveal>
                {!! $setting->termsHtml() !!}
            </div>
        @else
            <p class="shop-text-muted">Les conditions générales de la boutique seront bientôt disponibles ici.</p>
        @endif
    </div>
</x-shop-layout>
