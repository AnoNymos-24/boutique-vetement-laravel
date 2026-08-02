<x-guest-layout>
    <h1 class="h2 auth-card__title">Créer un accès équipe</h1>
    <p class="auth-card__subtitle">Pour l'admin ou un membre de l'équipe vente.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-lg">
        @csrf

        <div class="form-group">
            <x-input-label for="name" value="Nom" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="form-group">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="form-group">
            <x-input-label for="password" value="Mot de passe" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="form-group">
            <x-input-label for="password_confirmation" value="Confirmer le mot de passe" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="flex items-center justify-between mt-lg">
            <a class="link" href="{{ route('login') }}">Déjà inscrit ?</a>
            <x-primary-button>S'inscrire</x-primary-button>
        </div>
    </form>
</x-guest-layout>
