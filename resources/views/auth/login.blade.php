<x-guest-layout>
    <h1 class="h2 auth-card__title">Connexion</h1>
    <p class="auth-card__subtitle">Accédez au back-office de la boutique.</p>

    <x-auth-session-status class="mt-md" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-lg">
        @csrf

        <div class="form-group">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="vous@boutique.test" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="form-group">
            <x-input-label for="password" value="Mot de passe" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="flex items-center justify-between mt-lg">
            <div class="checkbox-row">
                <input id="remember_me" type="checkbox" name="remember">
                <label for="remember_me">Se souvenir de moi</label>
            </div>

            @if (Route::has('password.request'))
                <a class="link" href="{{ route('password.request') }}">Mot de passe oublié ?</a>
            @endif
        </div>

        <x-primary-button class="w-full mt-lg">
            Se connecter
        </x-primary-button>
    </form>

    @if (Route::has('register'))
        <p class="auth-card__footer">
            Pas encore de compte ? <a class="link" href="{{ route('register') }}">Créer un accès équipe</a>
        </p>
    @endif
</x-guest-layout>
