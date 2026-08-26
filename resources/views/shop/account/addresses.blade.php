<x-shop-layout>
    <div class="shop-container" style="padding: var(--shop-space-lg) var(--shop-space-lg) var(--shop-space-3xl);">
        <x-shop.account-layout active="addresses">
            <div style="margin-bottom: var(--shop-space-lg);" data-reveal>
                <p class="shop-eyebrow">Livraison</p>
                <p class="shop-text-muted">Gérez les adresses utilisées à la commande.</p>
            </div>

            <div class="account-card shop-card" data-reveal>
                <h3 class="shop-h3" style="margin-bottom: var(--shop-space-md);">Mes adresses</h3>

                @forelse ($addresses as $address)
                    <div class="address-card {{ $address->is_default ? 'is-default' : '' }}">
                        @if ($address->is_default)
                            <span class="shop-badge shop-badge--success address-card__default-badge">Par défaut</span>
                        @endif
                        <strong>{{ $address->label }}</strong>
                        <p class="shop-text-muted" style="margin-top:4px;">{{ $address->full_address }}</p>

                        <div class="shop-flex shop-gap-sm" style="margin-top: var(--shop-space-sm);">
                            @if (! $address->is_default)
                                <form method="POST" action="{{ route('customer.addresses.update', $address) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="label" value="{{ $address->label }}">
                                    <input type="hidden" name="full_address" value="{{ $address->full_address }}">
                                    <input type="hidden" name="is_default" value="1">
                                    <button type="submit" class="shop-btn shop-btn--secondary shop-btn--sm">Définir par défaut</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('customer.addresses.destroy', $address) }}" onsubmit="return confirm('Supprimer cette adresse ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="shop-btn shop-btn--ghost shop-btn--sm">Supprimer</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="shop-text-muted">Aucune adresse enregistrée.</p>
                @endforelse
            </div>

            <div class="account-card shop-card" data-reveal>
                <h3 class="shop-h3" style="margin-bottom: var(--shop-space-md);">Ajouter une adresse</h3>

                <form method="POST" action="{{ route('customer.addresses.store') }}">
                    @csrf

                    <div class="shop-field">
                        <label class="shop-label" for="label">Nom de l'adresse</label>
                        <input type="text" id="label" name="label" class="shop-input" placeholder="Ex. Domicile, Bureau…" required value="{{ old('label') }}">
                        <x-input-error :messages="$errors->get('label')" />
                    </div>

                    <div class="shop-field">
                        <label class="shop-label" for="full_address">Adresse complète</label>
                        <textarea id="full_address" name="full_address" class="shop-textarea" rows="2" required>{{ old('full_address') }}</textarea>
                        <x-input-error :messages="$errors->get('full_address')" />
                    </div>

                    <label class="filter-option" style="margin-bottom: var(--shop-space-md);">
                        <input type="checkbox" name="is_default" value="1"> Définir comme adresse par défaut
                    </label>

                    <button type="submit" class="shop-btn shop-btn--primary">Ajouter</button>
                </form>
            </div>
        </x-shop.account-layout>
    </div>
</x-shop-layout>
