<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}">
    @csrf
    @method('patch')

    <div class="form-group">
        <x-input-label for="name" value="Nom" />
        <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus autocomplete="name" />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div class="form-group">
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" />
        <x-input-error :messages="$errors->get('email')" />

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <p class="form-hint">
                Votre adresse email n'est pas vérifiée.
                <button form="send-verification" class="link">Renvoyer l'email de vérification.</button>
            </p>

            @if (session('status') === 'verification-link-sent')
                <p class="form-hint" style="color: var(--color-accent);">Un nouveau lien de vérification a été envoyé.</p>
            @endif
        @endif
    </div>

    <div class="flex items-center gap-md">
        <x-primary-button>Enregistrer</x-primary-button>

        @if (session('status') === 'profile-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-muted text-sm">
                Enregistré.
            </p>
        @endif
    </div>
</form>
