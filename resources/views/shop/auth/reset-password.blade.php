<x-shop.auth-card quote="Presque fini. Choisissez un nouveau mot de passe.">
    <h1 class="shop-h3" style="margin-bottom: var(--shop-space-lg);">Réinitialiser le mot de passe</h1>

    <form method="POST" action="{{ route('customer.password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="shop-field">
            <label class="shop-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="shop-input" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" />
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

        <button type="submit" class="shop-btn shop-btn--primary shop-btn--full">Réinitialiser le mot de passe</button>
    </form>
</x-shop.auth-card>
