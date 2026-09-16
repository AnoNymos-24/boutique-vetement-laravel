<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <h1 class="h1">Produits</h1>
            <p class="text-muted text-sm">{{ $products->total() }} référence(s) au catalogue</p>
        </div>
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <x-icon name="plus" /> Nouveau produit
        </a>
    </div>

    <div data-reveal x-data="bulkSelect(@js($products->pluck('id')))">
        <div class="bulk-bar" x-ref="bulkBar" style="display:none;">
            <span class="bulk-bar__count" x-text="count"></span>
            <span>produit(s) sélectionné(s)</span>
            <div class="bulk-bar__actions">
                <button type="button" class="btn btn-sm btn-ghost" style="color:#fff;" @click="clear()">Annuler</button>
                <form :action="'{{ route('products.bulk-destroy') }}'" method="POST" x-ref="bulkDeleteForm"
                      @submit="if (!confirm(count + ' produit(s) seront supprimés définitivement. Confirmer ?')) $event.preventDefault();">
                    @csrf
                    @method('DELETE')
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>
                    <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" /> Supprimer</button>
                </form>
            </div>
        </div>

        <form method="GET" class="table-toolbar">
            <div class="search-input" style="max-width:280px;">
                <x-icon name="search" />
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="Nom ou SKU…">
            </div>

            <div class="table-filters">
                <select name="category" class="form-select" onchange="this.form.submit()">
                    <option value="">Toutes catégories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) request('category') === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>

                <select name="stock" class="form-select" onchange="this.form.submit()">
                    <option value="">Tout le stock</option>
                    <option value="bas" @selected(request('stock') === 'bas')>Stock bas</option>
                    <option value="rupture" @selected(request('stock') === 'rupture')>Rupture</option>
                </select>

                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Tout statut</option>
                    <option value="active" @selected(request('status') === 'active')>Actif</option>
                    <option value="draft" @selected(request('status') === 'draft')>Brouillon</option>
                    <option value="archived" @selected(request('status') === 'archived')>Archivé</option>
                </select>

                <button type="submit" class="btn btn-secondary btn-sm">Filtrer</button>
                @if (request()->anyFilled(['search', 'category', 'stock', 'status']))
                    <a href="{{ route('products.index') }}" class="btn btn-ghost btn-sm">Réinitialiser</a>
                @endif
            </div>
        </form>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:36px;">
                            <input type="checkbox" :checked="allSelected" @change="toggleAll()">
                        </th>
                        <th>Produit</th>
                        <th>Variantes</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Statut</th>
                        <th class="cell-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                <input type="checkbox" value="{{ $product->id }}" x-model="selected">
                            </td>
                            <td class="cell-primary">
                                <div class="flex items-center gap-md">
                                    @if ($product->images->first())
                                        <img src="{{ $product->images->first()->url() }}" alt="" class="product-thumb">
                                    @else
                                        <span class="product-thumb product-thumb--empty"><x-icon name="shirt" /></span>
                                    @endif
                                    {{ $product->name }}
                                </div>
                            </td>
                            <td class="cell-muted font-mono text-sm" title="{{ $product->variants->pluck('sku')->implode(', ') }}">
                                {{ $product->variants->count() }} variante(s)
                            </td>
                            <td>{{ $product->category->name }}</td>
                            <td class="cell-numeric">
                                {{ $product->priceFormatted() }}
                                @if ($product->compare_at_price)
                                    <div class="text-xs text-muted" style="text-decoration:line-through;">
                                        {{ \App\Models\StoreSetting::current()->formatMoney($product->compare_at_price) }}
                                    </div>
                                @endif
                            </td>
                            <td class="cell-numeric">{{ $product->totalStock() }}</td>
                            <td>
                                @switch($product->stockState())
                                    @case('rupture')
                                        <span class="badge badge--destructive"><x-icon name="warning" /> Rupture</span>
                                        @break
                                    @case('bas')
                                        <span class="badge badge--warning"><x-icon name="warning" /> Stock bas</span>
                                        @break
                                    @default
                                        <span class="badge badge--success"><x-icon name="check" /> En stock</span>
                                @endswitch
                            </td>
                            <td class="cell-actions">
                                <form method="POST" action="{{ route('products.destroy', $product) }}"
                                      onsubmit="return confirm('Supprimer « {{ addslashes($product->name) }} » définitivement ?');"
                                      style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-icon" title="Supprimer">
                                        <x-icon name="trash" />
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-muted text-sm" style="text-align:center; padding: var(--space-3xl);">
                                Aucun produit ne correspond à ces filtres.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $products->appends(request()->query())->links('pagination.custom') }}
        </div>
    </div>
</x-app-layout>
