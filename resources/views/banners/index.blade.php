<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <h1 class="h1">Bannières</h1>
            <p class="text-muted text-sm">Visuels de la page d'accueil boutique — plusieurs bannières actives s'affichent en carrousel.</p>
        </div>
        <a href="{{ route('banners.create') }}" class="btn btn-primary">
            <x-icon name="plus" /> Nouvelle bannière
        </a>
    </div>

    <div class="table-wrapper" data-reveal>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Bannière</th>
                    <th>Ordre</th>
                    <th>Période</th>
                    <th>Statut</th>
                    <th class="cell-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($banners as $banner)
                    <tr>
                        <td class="cell-primary">
                            <div class="flex items-center gap-md">
                                <img src="{{ $banner->imageUrl() }}" alt="" class="product-thumb" style="width:64px; height:40px; object-fit:cover;">
                                <div>
                                    <div>{{ $banner->title }}</div>
                                    @if ($banner->subtitle)
                                        <div class="text-xs text-muted">{{ $banner->subtitle }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="cell-numeric">{{ $banner->sort_order }}</td>
                        <td class="cell-muted text-sm">
                            {{ $banner->starts_at?->format('d/m/Y') ?? '—' }} → {{ $banner->expires_at?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td>
                            @switch($banner->statusLabel())
                                @case('Active')
                                    <span class="badge badge--success"><x-icon name="check" /> Active</span>
                                    @break
                                @case('Programmée')
                                    <span class="badge badge--warning">Programmée</span>
                                    @break
                                @case('Expirée')
                                    <span class="badge badge--destructive">Expirée</span>
                                    @break
                                @default
                                    <span class="badge badge--muted">Inactive</span>
                            @endswitch
                        </td>
                        <td class="cell-actions">
                            <div class="flex gap-sm" style="justify-content:flex-end;">
                                <a href="{{ route('banners.edit', $banner) }}" class="btn btn-ghost btn-icon" title="Modifier"><x-icon name="edit" /></a>
                                <form method="POST" action="{{ route('banners.toggle', $banner) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-secondary btn-sm">
                                        {{ $banner->is_active ? 'Désactiver' : 'Activer' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('banners.destroy', $banner) }}"
                                      onsubmit="return confirm('Supprimer la bannière « {{ addslashes($banner->title) }} » ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-icon" title="Supprimer"><x-icon name="trash" /></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-muted text-sm" style="text-align:center; padding: var(--space-3xl);">
                            Aucune bannière pour le moment — la boutique affichera le hero par défaut.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
