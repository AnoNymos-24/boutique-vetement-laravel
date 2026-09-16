<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <h1 class="h1">Stock</h1>
            <p class="text-muted text-sm">Niveaux de stock, alertes et mouvements.</p>
        </div>
        <a href="{{ route('stock.history') }}" class="btn btn-secondary">
            <x-icon name="chart" /> Historique des mouvements
        </a>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card" style="--kpi-accent: var(--color-primary)">
            <div class="kpi-card__label"><x-icon name="box" /> Références en catalogue</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['references'] }}">0</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-destructive)">
            <div class="kpi-card__label"><x-icon name="warning" /> Ruptures de stock</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['ruptures'] }}">0</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-warning)">
            <div class="kpi-card__label"><x-icon name="warning" /> Stock bas</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['stock_bas'] }}">0</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-accent)">
            <div class="kpi-card__label"><x-icon name="cash" /> Valeur du stock</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['valeur_stock'] / 100 }}" data-count-decimals="0" data-count-suffix=" {{ $currency['symbol'] }}">0 {{ $currency['symbol'] }}</div>
        </div>
    </div>

    <div data-reveal x-data="stockAdjuster()">
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

                <button type="submit" class="btn btn-secondary btn-sm">Filtrer</button>
                @if (request()->anyFilled(['search', 'category', 'stock']))
                    <a href="{{ route('stock') }}" class="btn btn-ghost btn-sm">Réinitialiser</a>
                @endif
            </div>
        </form>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>SKU</th>
                        <th>Catégorie</th>
                        <th>Stock actuel</th>
                        <th>Seuil d'alerte</th>
                        <th>Statut</th>
                        <th class="cell-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($variants as $variant)
                        <tr>
                            <td class="cell-primary">
                                <div class="flex items-center gap-md">
                                    @if ($variant->product->images->first())
                                        <img src="{{ $variant->product->images->first()->url() }}" alt="" class="product-thumb">
                                    @else
                                        <span class="product-thumb product-thumb--empty"><x-icon name="shirt" /></span>
                                    @endif
                                    <div>
                                        <div>{{ $variant->product->name }}</div>
                                        <div class="text-muted text-sm">{{ $variant->label() }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="cell-muted font-mono text-sm">{{ $variant->sku }}</td>
                            <td>{{ $variant->product->category->name }}</td>
                            <td class="cell-numeric font-semibold">{{ $variant->stock_quantity }}</td>
                            <td class="cell-numeric text-muted">{{ $variant->low_stock_threshold }}</td>
                            <td>
                                @switch($variant->stockState())
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
                                <div class="flex gap-sm" style="justify-content:flex-end;">
                                    <a href="{{ route('stock.history', ['product_variant_id' => $variant->id]) }}" class="btn btn-ghost btn-icon" title="Historique">
                                        <x-icon name="chart" />
                                    </a>
                                    <button type="button" class="btn btn-secondary btn-sm"
                                            @click="open({{ $variant->id }}, @js($variant->product->name . ' — ' . $variant->label()), {{ $variant->stock_quantity }})">
                                        <x-icon name="edit" /> Ajuster
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-muted text-sm" style="text-align:center; padding: var(--space-3xl);">
                                Aucune variante ne correspond à ces filtres.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $variants->appends(request()->query())->links('pagination.custom') }}
        </div>

        <x-modal name="stock-adjust" focusable>
            <form method="POST" action="{{ route('stock.movements.store') }}" style="padding: var(--space-2xl);">
                @csrf
                <h2 class="h3" style="margin-bottom: var(--space-lg);">
                    Ajuster le stock — <span x-text="productName"></span>
                </h2>

                <input type="hidden" name="product_variant_id" :value="productId">

                <div class="form-group">
                    <x-input-label value="Stock actuel" />
                    <p class="font-mono" style="font-size:1.25rem; font-weight:700;" x-text="currentStock"></p>
                </div>

                <div class="form-group">
                    <x-input-label for="type" value="Type de mouvement" />
                    <select id="type" name="type" class="form-select" x-model="type" required>
                        <option value="entree">Entrée (réassort, retour…)</option>
                        <option value="sortie">Sortie (casse, perte…)</option>
                        <option value="ajustement">Ajustement inventaire</option>
                    </select>
                </div>

                <div class="form-group">
                    <x-input-label for="quantity" x-text="quantityLabel">Quantité entrante</x-input-label>
                    <x-text-input id="quantity" type="number" min="0" name="quantity" required autofocus />
                </div>

                <div class="form-group">
                    <x-input-label for="note" value="Note (optionnel)" />
                    <textarea id="note" name="note" class="form-textarea" rows="2" placeholder="Ex. Livraison fournisseur, retour client…"></textarea>
                </div>

                <div class="flex gap-md">
                    <button type="submit" class="btn btn-primary"><x-icon name="check" /> Valider le mouvement</button>
                    <button type="button" class="btn btn-secondary" @click="$dispatch('close')">Annuler</button>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>
