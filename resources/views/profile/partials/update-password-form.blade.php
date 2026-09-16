<p class="text-muted text-sm" style="margin-bottom: var(--space-lg);">Utilisez un mot de passe long et unique pour rester en sécurité.</p>

<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    <div class="form-group">
        <x-input-label for="update_password_current_password" value="Mot de passe actuel" />
        <x-text-input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" />
        <x-input-error :messages="$errors->updatePassword->get('current_password')" />
    </div>

    <div class="form-group">
        <x-input-label for="update_password_password" value="Nouveau mot de passe" />
        <x-text-input id="update_password_password" name="password" type="password" autocomplete="new-password" />
        <x-input-error :messages="$errors->updatePassword->get('password')" />
    </div>

    <div class="form-group">
        <x-input-label for="update_password_password_confirmation" value="Confirmer le mot de passe" />
        <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
    </div>

    <div class="flex items-center gap-md">
        <x-primary-button>Enregistrer</x-primary-button>

        @if (session('status') === 'password-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-muted text-sm">
                Enregistré.
            </p>
        @endif
    </div>
</form>
