<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <h1 class="h1">Paramètres</h1>
            <p class="text-muted text-sm">Identité, catégories et étiquettes de la boutique.</p>
        </div>
    </div>

    @include('settings._tabs')

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: var(--space-xl);">
        <div>
            <div class="card" data-reveal>
                <div class="card-header">
                    <h2 class="card-title">Étiquettes produit</h2>
                </div>
                <p class="text-muted text-sm mt-sm" style="margin-bottom: var(--space-lg);">
                    Badges mis en avant sur la fiche produit (ex. Nouveauté, Coup de cœur, Édition limitée).
                </p>

                <form method="POST" action="{{ route('settings.taxonomies.store', 'etiquettes') }}" class="flex gap-md" style="align-items:flex-start; margin-bottom: var(--space-xl);">
                    @csrf
                    <div class="form-group" style="flex:1; margin-bottom:0;">
                        <x-text-input name="name" placeholder="Ex. Nouveauté" required />
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><x-icon name="plus" /> Ajouter</button>
                </form>

                <div style="display:flex; flex-direction:column; gap:2px;">
                    @forelse ($labels as $label)
                        <div class="flex items-center justify-between" style="padding: var(--space-sm) 0; border-bottom: 1px solid var(--color-muted);">
                            <div class="flex items-center gap-md">
                                <span class="font-semibold text-sm">{{ $label->name }}</span>
                                <span class="badge badge--muted">{{ $label->products_count }}</span>
                            </div>
                            <form method="POST" action="{{ route('settings.taxonomies.destroy', ['etiquettes', $label->id]) }}"
                                  onsubmit="return confirm('Supprimer l\'étiquette « {{ addslashes($label->name) }} » ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-icon" title="Supprimer"><x-icon name="trash" /></button>
                            </form>
                        </div>
                    @empty
                        <p class="text-muted text-sm">Aucune étiquette pour le moment.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div>
            <div class="card" data-reveal>
                <div class="card-header">
                    <h2 class="card-title">Tags (référencement)</h2>
                </div>
                <p class="text-muted text-sm mt-sm" style="margin-bottom: var(--space-lg);">
                    Mots-clés utilisés pour la recherche et le référencement des produits.
                </p>

                <form method="POST" action="{{ route('settings.taxonomies.store', 'tags') }}" class="flex gap-md" style="align-items:flex-start; margin-bottom: var(--space-xl);">
                    @csrf
                    <div class="form-group" style="flex:1; margin-bottom:0;">
                        <x-text-input name="name" placeholder="Ex. coton bio" required />
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><x-icon name="plus" /> Ajouter</button>
                </form>

                <div style="display:flex; flex-direction:column; gap:2px;">
                    @forelse ($tags as $tag)
                        <div class="flex items-center justify-between" style="padding: var(--space-sm) 0; border-bottom: 1px solid var(--color-muted);">
                            <div class="flex items-center gap-md">
                                <span class="font-semibold text-sm">{{ $tag->name }}</span>
                                <span class="badge badge--muted">{{ $tag->products_count }}</span>
                            </div>
                            <form method="POST" action="{{ route('settings.taxonomies.destroy', ['tags', $tag->id]) }}"
                                  onsubmit="return confirm('Supprimer le tag « {{ addslashes($tag->name) }} » ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-icon" title="Supprimer"><x-icon name="trash" /></button>
                            </form>
                        </div>
                    @empty
                        <p class="text-muted text-sm">Aucun tag pour le moment.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
