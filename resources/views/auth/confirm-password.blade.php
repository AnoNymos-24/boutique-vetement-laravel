<x-guest-layout>
    <h1 class="h2 auth-card__title">Confirmer le mot de passe</h1>
    <p class="auth-card__subtitle">Zone sécurisée — merci de confirmer votre mot de passe avant de continuer.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-lg">
        @csrf

        <div class="form-group">
            <x-input-label for="password" value="Mot de passe" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" autofocus />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="flex justify-end mt-lg">
            <x-primary-button>Confirmer</x-primary-button>
        </div>
    </form>
</x-guest-layout>
