<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <h1 class="h1">Mon profil</h1>
            <p class="text-muted text-sm">Informations personnelles et sécurité du compte.</p>
        </div>
    </div>

    <div class="card" data-reveal style="max-width:560px; margin-bottom: var(--space-xl);">
        <div class="card-header">
            <h2 class="card-title">Informations du profil</h2>
        </div>
        @include('profile.partials.update-profile-information-form')
    </div>

    <div class="card" data-reveal style="max-width:560px; margin-bottom: var(--space-xl);">
        <div class="card-header">
            <h2 class="card-title">Mot de passe</h2>
        </div>
        @include('profile.partials.update-password-form')
    </div>

    <div class="card" data-reveal style="max-width:560px; border-color: var(--color-destructive);">
        <div class="card-header">
            <h2 class="card-title">Supprimer le compte</h2>
        </div>
        @include('profile.partials.delete-user-form')
    </div>
</x-app-layout>
