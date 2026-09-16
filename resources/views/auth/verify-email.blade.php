<x-guest-layout>
    <h1 class="h2 auth-card__title">Vérifier l'email</h1>
    <p class="auth-card__subtitle">
        Merci de vérifier votre adresse en cliquant sur le lien reçu par email. Si vous ne l'avez pas reçu, nous pouvons vous en renvoyer un.
    </p>

    @if (session('status') == 'verification-link-sent')
        <x-auth-session-status class="mt-md" status="Un nouveau lien de vérification a été envoyé à votre adresse email." />
    @endif

    <div class="flex items-center justify-between mt-lg">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>Renvoyer l'email de vérification</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="link">Déconnexion</button>
        </form>
    </div>
</x-guest-layout>
