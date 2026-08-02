<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <div class="breadcrumb">
                <a href="{{ route('stock') }}" class="link">Stock</a>
                <x-icon name="chevron-right" />
                <span>Historique</span>
            </div>
            <h1 class="h1">Historique des mouvements</h1>
            @if (request('product_variant_id') && $movements->isNotEmpty())
                <p class="text-muted text-sm">Filtré sur « {{ $movements->first()->productVariant->product->name }} — {{ $movements->first()->productVariant->label() }} »</p>
            @endif
        </div>
        <a href="{{ route('stock') }}" class="btn btn-secondary">
            <x-icon name="box" /> Retour au stock
        </a>
    </div>

    <div data-reveal>
        <form method="GET" class="table-toolbar">
            <div class="table-filters">
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous types</option>
                    <option value="entree" @selected(request('type') === 'entree')>Entrée</option>
                    <option value="sortie" @selected(request('type') === 'sortie')>Sortie</option>
                    <option value="ajustement" @selected(request('type') === 'ajustement')>Ajustement</option>
                </select>

                @if (request()->anyFilled(['type', 'product_variant_id']))
                    <a href="{{ route('stock.history') }}" class="btn btn-ghost btn-sm">Réinitialiser</a>
                @endif
            </div>
        </form>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Produit</th>
                        <th>Type</th>
                        <th>Quantité</th>
                        <th>Stock après</th>
                        <th>Par</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        <tr>
                            <td class="cell-muted text-sm">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                            <td class="cell-primary">
                                {{ $movement->productVariant->product->name }}
                                <span class="text-muted text-sm">— {{ $movement->productVariant->label() }}</span>
                            </td>
                            <td>
                                @switch($movement->type)
                                    @case('entree')
                                        <span class="badge badge--success">Entrée</span>
                                        @break
                                    @case('sortie')
                                        <span class="badge badge--destructive">Sortie</span>
                                        @break
                                    @default
                                        <span class="badge badge--muted">Ajustement</span>
                                @endswitch
                            </td>
                            <td class="cell-numeric font-semibold" style="color: {{ $movement->quantity_change >= 0 ? 'var(--color-accent)' : 'var(--color-destructive)' }};">
                                {{ $movement->quantity_change >= 0 ? '+' : '' }}{{ $movement->quantity_change }}
                            </td>
                            <td class="cell-numeric">{{ $movement->stock_after }}</td>
                            <td class="cell-muted text-sm">{{ $movement->user?->name ?? '—' }}</td>
                            <td class="cell-muted text-sm">{{ $movement->note ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-muted text-sm" style="text-align:center; padding: var(--space-3xl);">
                                Aucun mouvement enregistré pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $movements->appends(request()->query())->links('pagination.custom') }}
        </div>
    </div>
</x-app-layout>
