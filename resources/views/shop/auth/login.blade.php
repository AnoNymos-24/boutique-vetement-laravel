<x-shop.auth-card quote="Contente de vous revoir.">
    <h1 class="shop-h3" style="margin-bottom: var(--shop-space-xs);">Connexion</h1>
    <p class="shop-text-muted" style="font-size:0.875rem; margin-bottom: var(--shop-space-lg);">Accédez à votre compte client.</p>

    @if (session('status'))
        <div class="shop-status-banner">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('customer.login') }}">
        @csrf
        @if ($redirectTo ?? null)
            <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
        @endif

        <div class="shop-field">
            <label class="shop-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="shop-input" value="{{ old('email') }}" required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="shop-field">
            <label class="shop-label" for="password">Mot de passe</label>
            <input type="password" id="password" name="password" class="shop-input" required autocomplete="current-password">
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="shop-flex shop-justify-between shop-items-center" style="margin-bottom: var(--shop-space-lg);">
            <label class="filter-option" style="padding:0;">
                <input type="checkbox" name="remember"> Se souvenir de moi
            </label>
            <a href="{{ route('customer.password.request') }}" class="shop-text-muted" style="font-size:0.8125rem;">Mot de passe oublié ?</a>
        </div>

        <button type="submit" class="shop-btn shop-btn--primary shop-btn--full">Se connecter</button>
    </form>

    <p class="shop-text-muted" style="text-align:center; font-size:0.875rem; margin-top: var(--shop-space-lg);">
        Pas encore de compte ? <a href="{{ route('customer.register') }}" style="color:var(--shop-color-primary); font-weight:600;">Créer un compte</a>
    </p>
</x-shop.auth-card>
