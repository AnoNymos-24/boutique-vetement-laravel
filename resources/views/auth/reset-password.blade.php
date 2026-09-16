<x-guest-layout>
    <h1 class="h2 auth-card__title">Réinitialiser le mot de passe</h1>
    <p class="auth-card__subtitle">Choisissez un nouveau mot de passe pour votre compte.</p>

    <form method="POST" action="{{ route('password.store') }}" class="mt-lg">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="form-group">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="form-group">
            <x-input-label for="password" value="Nouveau mot de passe" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="form-group">
            <x-input-label for="password_confirmation" value="Confirmer le mot de passe" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="flex justify-end mt-lg">
            <x-primary-button>Réinitialiser le mot de passe</x-primary-button>
        </div>
    </form>
</x-guest-layout>
