<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <h1 class="h1">Paramètres</h1>
            <p class="text-muted text-sm">Identité, catégories et étiquettes de la boutique.</p>
        </div>
    </div>

    @include('settings._tabs')

    <div class="card" data-reveal style="max-width:560px;">
        <div class="card-header">
            <h2 class="card-title">Ajouter une catégorie</h2>
        </div>

        <form method="POST" action="{{ route('settings.categories.store') }}" class="flex gap-md" style="align-items:flex-start;">
            @csrf
            <div class="form-group" style="flex:1; margin-bottom:0;">
                <x-text-input name="name" placeholder="Ex. Chaussures" required autofocus />
                <x-input-error :messages="$errors->get('name')" />
            </div>
            <button type="submit" class="btn btn-primary"><x-icon name="plus" /> Ajouter</button>
        </form>
    </div>

    <div class="card" data-reveal style="max-width:560px; margin-top: var(--space-xl);">
        <div class="card-header">
            <h2 class="card-title">{{ $categories->count() }} catégorie(s)</h2>
        </div>

        <div style="display:flex; flex-direction:column; gap:2px;">
            @forelse ($categories as $category)
                <div class="flex items-center justify-between" style="padding: var(--space-md) 0; border-bottom: 1px solid var(--color-muted);">
                    <div class="flex items-center gap-md">
                        <span class="font-semibold">{{ $category->name }}</span>
                        <span class="badge badge--muted">{{ $category->products_count }} produit(s)</span>
                    </div>
                    <form method="POST" action="{{ route('settings.categories.destroy', $category) }}"
                          onsubmit="return confirm('Supprimer la catégorie « {{ addslashes($category->name) }} » ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-ghost btn-icon" title="Supprimer">
                            <x-icon name="trash" />
                        </button>
                    </form>
                </div>
            @empty
                <p class="text-muted text-sm">Aucune catégorie pour le moment.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
