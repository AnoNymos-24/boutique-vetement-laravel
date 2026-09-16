<x-guest-layout>
    <h1 class="h2 auth-card__title">Mot de passe oublié</h1>
    <p class="auth-card__subtitle">Indiquez votre email, nous vous envoyons un lien pour en choisir un nouveau.</p>

    <x-auth-session-status class="mt-md" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-lg">
        @csrf

        <div class="form-group">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="flex justify-end mt-lg">
            <x-primary-button>Envoyer le lien</x-primary-button>
        </div>
    </form>
</x-guest-layout>
