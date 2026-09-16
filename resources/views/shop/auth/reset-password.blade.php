<x-shop.auth-card quote="Presque fini. Choisissez un nouveau mot de passe.">
    <p class="shop-eyebrow" style="margin-bottom: var(--shop-space-xs);">Récupération</p>
    <h1 class="shop-h2" style="margin-bottom: var(--shop-space-xl);">Réinitialiser le mot de passe</h1>

    <form method="POST" action="{{ route('customer.password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="shop-field">
            <label class="shop-label" for="email">Email</label>
            <div class="auth-input-icon">
                <x-icon name="mail" />
                <input type="email" id="email" name="email" class="shop-input" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
            </div>
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="shop-field">
            <label class="shop-label" for="password">Mot de passe</label>
            <div class="auth-input-icon">
                <x-icon name="lock" />
                <input type="password" id="password" name="password" class="shop-input" required autocomplete="new-password">
            </div>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="shop-field">
            <label class="shop-label" for="password_confirmation">Confirmer le mot de passe</label>
            <div class="auth-input-icon">
                <x-icon name="lock" />
                <input type="password" id="password_confirmation" name="password_confirmation" class="shop-input" required autocomplete="new-password">
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <button type="submit" class="shop-btn shop-btn--primary shop-btn--full">Réinitialiser le mot de passe</button>
    </form>
</x-shop.auth-card>
