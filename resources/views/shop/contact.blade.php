<x-shop-layout>
    <div class="shop-container" style="padding: var(--shop-space-lg) var(--shop-space-lg) var(--shop-space-3xl); max-width: 640px;">
        <div style="margin-bottom: var(--shop-space-xl);" data-reveal>
            <p class="shop-eyebrow">Nous contacter</p>
            <h1 class="shop-h1" style="font-size: 2rem;">Une question avant d'acheter ?</h1>
            <p class="shop-text-muted" style="margin-top: var(--shop-space-sm);">Écrivez-nous, notre équipe vous répond rapidement.</p>
        </div>

        @if (session('status'))
            <div class="shop-status-banner" data-reveal>{{ session('status') }}</div>
        @endif

        <div class="shop-card" style="padding: var(--shop-space-xl);" data-reveal>
            <form method="POST" action="{{ route('shop.contact.store') }}">
                @csrf

                <div class="shop-field">
                    <label class="shop-label" for="name">Nom complet</label>
                    <input type="text" id="name" name="name" class="shop-input" required
                           value="{{ old('name', $customer?->name) }}">
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div class="shop-field">
                    <label class="shop-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="shop-input" required
                           value="{{ old('email', $customer?->email) }}">
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <div class="shop-field">
                    <label class="shop-label" for="phone">Téléphone (optionnel)</label>
                    <input type="tel" id="phone" name="phone" class="shop-input" inputmode="tel"
                           value="{{ old('phone', $customer?->phone) }}">
                </div>

                <div class="shop-field">
                    <label class="shop-label" for="subject">Sujet</label>
                    <input type="text" id="subject" name="subject" class="shop-input" required maxlength="255"
                           value="{{ old('subject') }}" placeholder="Ex. Disponibilité d'une taille">
                    <x-input-error :messages="$errors->get('subject')" />
                </div>

                <div class="shop-field">
                    <label class="shop-label" for="body">Votre message</label>
                    <textarea id="body" name="body" class="shop-textarea" rows="5" required maxlength="2000">{{ old('body') }}</textarea>
                    <x-input-error :messages="$errors->get('body')" />
                </div>

                <button type="submit" class="shop-btn shop-btn--primary">Envoyer le message</button>
            </form>
        </div>
    </div>
</x-shop-layout>
