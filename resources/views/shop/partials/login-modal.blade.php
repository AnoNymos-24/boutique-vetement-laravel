@guest('customer')
    <x-modal name="customer-login" :show="$errors->has('email')" focusable>
        <div style="padding: var(--shop-space-2xl);">
            <p class="shop-eyebrow" style="margin-bottom: var(--shop-space-xs);">Bienvenue</p>
            <h2 class="shop-h2" style="margin-bottom: var(--shop-space-xs);">Connexion</h2>
            <p class="shop-text-muted" style="font-size:0.875rem; margin-bottom: var(--shop-space-lg);">
                Accédez à votre compte pour commander et suivre vos achats.
            </p>

            <form method="POST" action="{{ route('customer.login') }}">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

                <div class="shop-field">
                    <label class="shop-label" for="modal-email">Email</label>
                    <div class="auth-input-icon">
                        <x-icon name="mail" />
                        <input type="email" id="modal-email" name="email" class="shop-input" required autocomplete="username" value="{{ old('email') }}">
                    </div>
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <div class="shop-field">
                    <label class="shop-label" for="modal-password">Mot de passe</label>
                    <div class="auth-input-icon">
                        <x-icon name="lock" />
                        <input type="password" id="modal-password" name="password" class="shop-input" required autocomplete="current-password">
                    </div>
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
        </div>
    </x-modal>
@endguest
