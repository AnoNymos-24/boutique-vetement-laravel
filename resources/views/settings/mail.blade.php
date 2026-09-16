<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <h1 class="h1">Paramètres</h1>
            <p class="text-muted text-sm">Identité, catégories, étiquettes et e-mail de la boutique.</p>
        </div>
    </div>

    @include('settings._tabs')

    <div class="card" data-reveal style="max-width:560px;">
        <div class="card-header">
            <h2 class="card-title">Compte e-mail des notifications</h2>
        </div>

        <p class="text-muted text-sm" style="margin-bottom: var(--space-lg);">
            Adresse Gmail utilisée pour envoyer les notifications à l'équipe (nouvelle commande, réclamation,
            message, action importante). Modifiable à tout moment — aucune intervention technique nécessaire.
        </p>

        @if (! $setting->hasMailConfigured())
            <div class="status-banner" style="margin-bottom: var(--space-lg);">
                <x-icon name="warning" /> Aucun compte configuré : les notifications restent en mode journal (aucun e-mail n'est réellement envoyé).
            </div>
        @endif

        <form method="POST" action="{{ route('settings.mail.update') }}" id="mail-settings-form">
            @csrf
            @method('PUT')

            <div class="form-group">
                <x-input-label for="mail_username" value="Adresse Gmail" />
                <x-text-input id="mail_username" type="email" name="mail_username" :value="old('mail_username', $setting->mail_username)" required placeholder="votreboutique@gmail.com" />
                <x-input-error :messages="$errors->get('mail_username')" />
            </div>

            <div class="form-group">
                <x-input-label for="mail_from_name" value="Nom de l'expéditeur" />
                <x-text-input id="mail_from_name" name="mail_from_name" :value="old('mail_from_name', $setting->mail_from_name)" placeholder="{{ $setting->name }}" />
                <p class="form-hint">Affiché comme expéditeur dans les e-mails reçus. Laisser vide pour utiliser le nom de la boutique.</p>
            </div>

            <div class="form-group">
                <x-input-label for="mail_password" value="Mot de passe d'application Gmail" />
                <x-text-input id="mail_password" type="password" name="mail_password" autocomplete="new-password"
                              placeholder="{{ $setting->hasMailConfigured() ? '••••••••••••••••' : '' }}" />
                <p class="form-hint">
                    {{ $setting->hasMailConfigured() ? 'Laisser vide pour conserver le mot de passe actuel. ' : '' }}
                    Ce n'est <strong>pas</strong> le mot de passe habituel du compte Gmail : il faut activer la validation
                    en deux étapes sur le compte Google, puis générer un « mot de passe d'application » dédié
                    (Compte Google → Sécurité → Validation en deux étapes → Mots de passe des applications).
                </p>
                <x-input-error :messages="$errors->get('mail_password')" />
            </div>
        </form>

        {{-- Formulaire séparé (pas de champ @method('PUT')) : le bouton "test" ci-dessous
             le cible via l'attribut form="…" plutôt que formaction, pour ne jamais hériter
             du spoofing PUT du formulaire d'enregistrement — sinon Laravel route la requête
             en PUT vers une route qui n'accepte que POST (bug réellement rencontré). --}}
        @if ($setting->hasMailConfigured())
            <form method="POST" action="{{ route('settings.mail.test') }}" id="mail-test-form">
                @csrf
            </form>
        @endif

        <div class="flex gap-md mt-xl">
            <button type="submit" form="mail-settings-form" class="btn btn-primary"><x-icon name="check" /> Enregistrer</button>

            @if ($setting->hasMailConfigured())
                <button type="submit" form="mail-test-form" class="btn btn-secondary">
                    <x-icon name="mail" /> Envoyer un e-mail de test
                </button>
            @endif
        </div>

        @if ($setting->hasMailConfigured())
            <form method="POST" action="{{ route('settings.mail.update') }}" class="mt-lg"
                  onsubmit="return confirm('Supprimer la configuration e-mail ? Les notifications repasseront en mode journal.');">
                @csrf
                @method('PUT')
                <input type="hidden" name="mail_username" value="{{ $setting->mail_username }}">
                <input type="hidden" name="remove_mail" value="1">
                <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--color-destructive);">Retirer la configuration</button>
            </form>
        @endif
    </div>
</x-app-layout>
