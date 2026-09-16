<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <h1 class="h1">Paramètres</h1>
            <p class="text-muted text-sm">Identité, catégories, étiquettes, e-mail, paiement et conditions générales de la boutique.</p>
        </div>
    </div>

    @include('settings._tabs')

    <div class="card" data-reveal style="max-width:820px;">
        <div class="card-header">
            <h2 class="card-title">Conditions générales d'utilisation et de vente</h2>
            <a href="{{ route('shop.terms') }}" target="_blank" class="link">Voir la page publique</a>
        </div>

        <p class="text-muted text-sm" style="margin-bottom: var(--space-lg);">
            Texte affiché sur la page publique "Conditions générales" de la boutique. Modifiable à tout moment —
            réservé à l'administrateur, contrairement au reste de Paramètres ouvert aux vendeurs. Prend en charge une
            syntaxe simple : <code># Titre</code>, <code>## Sous-titre</code>, <code>**gras**</code>, <code>- élément de liste</code>,
            <code>&gt; citation</code>, et <code>---</code> pour une ligne de séparation.
        </p>

        @if ($setting->terms_updated_at)
            <p class="text-muted text-sm" style="margin-bottom: var(--space-lg);">
                Dernière mise à jour : {{ $setting->terms_updated_at->translatedFormat('d F Y à H:i') }}
            </p>
        @endif

        <form method="POST" action="{{ route('settings.terms.update') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <x-input-label for="terms_content" value="Contenu" />
                <textarea id="terms_content" name="terms_content" class="form-textarea" rows="28"
                          style="font-family: var(--font-mono); font-size: 0.8125rem;" required>{{ old('terms_content', $setting->terms_content) }}</textarea>
                <x-input-error :messages="$errors->get('terms_content')" />
            </div>

            <div class="flex gap-md mt-xl">
                <button type="submit" class="btn btn-primary"><x-icon name="check" /> Enregistrer</button>
            </div>
        </form>
    </div>
</x-app-layout>
