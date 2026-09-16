<p class="text-muted text-sm" style="margin-bottom: var(--space-lg);">
    La suppression est définitive : toutes les données associées à ce compte seront perdues.
</p>

<x-danger-button x-data @click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
    Supprimer mon compte
</x-danger-button>

<x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
    <form method="post" action="{{ route('profile.destroy') }}" style="padding: var(--space-xl);">
        @csrf
        @method('delete')

        <h2 class="h3" style="margin-bottom: var(--space-sm);">Supprimer votre compte ?</h2>
        <p class="text-muted text-sm" style="margin-bottom: var(--space-lg);">
            Cette action est irréversible. Saisissez votre mot de passe pour confirmer.
        </p>

        <div class="form-group">
            <x-input-label for="password" value="Mot de passe" class="sr-only" />
            <x-text-input id="password" name="password" type="password" placeholder="Mot de passe" />
            <x-input-error :messages="$errors->userDeletion->get('password')" />
        </div>

        <div class="flex justify-end gap-md mt-lg">
            <x-secondary-button x-on:click="$dispatch('close')">Annuler</x-secondary-button>
            <x-danger-button>Supprimer mon compte</x-danger-button>
        </div>
    </form>
</x-modal>
