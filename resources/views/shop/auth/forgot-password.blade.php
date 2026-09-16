<x-shop.auth-card quote="Ça arrive. On vous envoie un lien pour repartir.">
    <p class="shop-eyebrow" style="margin-bottom: var(--shop-space-xs);">Récupération</p>
    <h1 class="shop-h2" style="margin-bottom: var(--shop-space-xs);">Mot de passe oublié</h1>
    <p class="shop-text-muted" style="font-size:0.9375rem; margin-bottom: var(--shop-space-xl);">Indiquez votre email, nous vous envoyons un lien de réinitialisation.</p>

    @if (session('status'))
        <div class="shop-status-banner">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('customer.password.email') }}">
        @csrf

        <div class="shop-field">
            <label class="shop-label" for="email">Email</label>
            <div class="auth-input-icon">
                <x-icon name="mail" />
                <input type="email" id="email" name="email" class="shop-input" value="{{ old('email') }}" required autofocus autocomplete="username">
            </div>
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <button type="submit" class="shop-btn shop-btn--primary shop-btn--full">Envoyer le lien de réinitialisation</button>
    </form>

    <p class="shop-text-muted" style="text-align:center; font-size:0.875rem; margin-top: var(--shop-space-lg);">
        <a href="{{ route('customer.login') }}" style="color:var(--shop-color-primary); font-weight:600;">Retour à la connexion</a>
    </p>
</x-shop.auth-card>
