@props(['quote' => 'Des pièces choisies pour vous accompagner, saison après saison.'])

<x-shop-layout>
    <div class="auth-overlay">
        <div class="auth-overlay__visual" style="--shop-cta-word: '{{ Str::upper($storeName ?? 'INA') }}';">
            <p class="auth-overlay__visual-quote">{{ $quote }}</p>
        </div>

        <div class="auth-overlay__form-side">
            <div class="auth-overlay__card shop-card" data-reveal>
                {{ $slot }}
            </div>
        </div>
    </div>
</x-shop-layout>
