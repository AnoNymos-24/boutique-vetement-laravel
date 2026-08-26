<x-shop.auth-card quote="Ça arrive. On vous envoie un lien pour repartir.">
    <h1 class="shop-h3" style="margin-bottom: var(--shop-space-xs);">Mot de passe oublié</h1>
    <p class="shop-text-muted" style="font-size:0.875rem; margin-bottom: var(--shop-space-lg);">Indiquez votre email, nous vous envoyons un lien de réinitialisation.</p>

    @if (session('status'))
        <div class="shop-status-banner">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('customer.password.email') }}">
        @csrf

        <div class="shop-field">
            <label class="shop-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="shop-input" value="{{ old('email') }}" required autofocus>
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <button type="submit" class="shop-btn shop-btn--primary shop-btn--full">Envoyer le lien de réinitialisation</button>
    </form>
</x-shop.auth-card>
