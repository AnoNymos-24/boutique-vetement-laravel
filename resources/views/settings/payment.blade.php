<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <h1 class="h1">Paramètres</h1>
            <p class="text-muted text-sm">Identité, catégories, étiquettes et e-mail de la boutique.</p>
        </div>
    </div>

    @include('settings._tabs')

    <div class="card" data-reveal style="max-width:640px;">
        <div class="card-header">
            <h2 class="card-title">Moyens de paiement</h2>
        </div>

        <p class="text-muted text-sm" style="margin-bottom: var(--space-lg);">
            Numéros affichés à la cliente sur le tunnel d'achat, et moyens de paiement activés ou non sur la
            boutique en ligne. Désactiver un moyen le retire immédiatement du formulaire de commande.
        </p>

        <form method="POST" action="{{ route('settings.payment.update') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="checkbox-row">
                    <input type="checkbox" name="orange_money_enabled" value="1" @checked(old('orange_money_enabled', $setting->orange_money_enabled))>
                    <span><strong>Orange Money</strong> — proposé au paiement</span>
                </label>
            </div>

            <div class="form-group">
                <x-input-label for="orange_money_number" value="Numéro Orange Money" />
                <x-text-input id="orange_money_number" name="orange_money_number" :value="old('orange_money_number', $setting->orange_money_number)" placeholder="07 00 00 00 00" />
                <p class="form-hint">Numéro affiché dans les instructions de paiement Orange Money.</p>
                <x-input-error :messages="$errors->get('orange_money_number')" />
            </div>

            <hr style="border:none; border-top:1px solid var(--color-border); margin: var(--space-lg) 0;">

            <div class="form-group">
                <label class="checkbox-row">
                    <input type="checkbox" name="wave_enabled" value="1" @checked(old('wave_enabled', $setting->wave_enabled))>
                    <span><strong>Wave</strong> — proposé au paiement</span>
                </label>
            </div>

            <div class="form-group">
                <x-input-label for="wave_number" value="Numéro Wave" />
                <x-text-input id="wave_number" name="wave_number" :value="old('wave_number', $setting->wave_number)" placeholder="07 00 00 00 00" />
                <p class="form-hint">Numéro affiché dans les instructions de paiement Wave.</p>
                <x-input-error :messages="$errors->get('wave_number')" />
            </div>

            <hr style="border:none; border-top:1px solid var(--color-border); margin: var(--space-lg) 0;">

            <div class="form-group">
                <label class="checkbox-row">
                    <input type="checkbox" name="cash_on_delivery_enabled" value="1" @checked(old('cash_on_delivery_enabled', $setting->cash_on_delivery_enabled))>
                    <span><strong>Paiement à la livraison</strong> — la cliente paie en espèces au livreur, aucun numéro requis</span>
                </label>
            </div>

            @if (! $setting->orange_money_enabled && ! $setting->wave_enabled && ! $setting->cash_on_delivery_enabled)
                <div class="status-banner status-banner--error" style="margin-bottom: var(--space-lg);">
                    <x-icon name="warning" /> Aucun moyen de paiement n'est actuellement activé : les clientes ne pourront pas finaliser de commande en ligne.
                </div>
            @endif

            <div class="flex gap-md mt-xl">
                <button type="submit" class="btn btn-primary"><x-icon name="check" /> Enregistrer</button>
            </div>
        </form>
    </div>
</x-app-layout>
