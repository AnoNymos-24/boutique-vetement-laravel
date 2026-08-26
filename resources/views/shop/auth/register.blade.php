<x-shop.auth-card quote="Rejoignez la garde-robe. Suivez vos commandes, retrouvez vos favoris.">
    <h1 class="shop-h3" style="margin-bottom: var(--shop-space-xs);">Créer un compte</h1>
    <p class="shop-text-muted" style="font-size:0.875rem; margin-bottom: var(--shop-space-lg);">Suivez vos commandes et retrouvez vos favoris.</p>

    <form method="POST" action="{{ route('customer.register') }}">
        @csrf

        <div class="shop-field">
            <label class="shop-label" for="name">Nom</label>
            <input type="text" id="name" name="name" class="shop-input" value="{{ old('name') }}" required autofocus autocomplete="name">
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="shop-field">
            <label class="shop-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="shop-input" value="{{ old('email') }}" required autocomplete="username">
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="shop-field">
            <label class="shop-label" for="phone">Téléphone</label>
            <input type="tel" id="phone" name="phone" class="shop-input" value="{{ old('phone') }}" required autocomplete="tel" inputmode="tel" placeholder="07…">
            <x-input-error :messages="$errors->get('phone')" />
        </div>

        <div class="shop-field">
            <label class="shop-label" for="password">Mot de passe</label>
            <input type="password" id="password" name="password" class="shop-input" required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="shop-field">
            <label class="shop-label" for="password_confirmation">Confirmer le mot de passe</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="shop-input" required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <button type="submit" class="shop-btn shop-btn--primary shop-btn--full">S'inscrire</button>
    </form>

    <p class="shop-text-muted" style="text-align:center; font-size:0.875rem; margin-top: var(--shop-space-lg);">
        Déjà un compte ? <a href="{{ route('customer.login') }}" style="color:var(--shop-color-primary); font-weight:600;">Se connecter</a>
    </p>
</x-shop.auth-card>
