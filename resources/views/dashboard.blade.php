<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <h1 class="h1">Tableau de bord</h1>
            <p class="text-muted text-sm">Vue d'ensemble de l'activité — {{ now()->translatedFormat('l d F Y') }}</p>
        </div>
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <x-icon name="plus" /> Nouveau produit
        </a>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card" style="--kpi-accent: var(--color-accent)">
            <div class="kpi-card__label"><x-icon name="cash" /> Chiffre d'affaires du jour</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['ca_jour'] / 100 }}" data-count-decimals="2" data-count-suffix=" €">0,00 €</div>
            @if ($kpis['ca_variation'] === null)
                <div class="kpi-card__trend is-flat">pas de vente hier</div>
            @elseif ($kpis['ca_variation'] >= 0)
                <div class="kpi-card__trend is-up"><x-icon name="trend-up" /> +{{ $kpis['ca_variation'] }}% vs hier</div>
            @else
                <div class="kpi-card__trend is-down"><x-icon name="trend-down" /> {{ $kpis['ca_variation'] }}% vs hier</div>
            @endif
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-primary)">
            <div class="kpi-card__label"><x-icon name="cart" /> Commandes à traiter</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['commandes_a_traiter'] }}">0</div>
            <div class="kpi-card__trend is-flat">
                <a href="{{ route('orders', ['status' => 'en_attente']) }}" class="link">à valider</a>
            </div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-warning)">
            <div class="kpi-card__label"><x-icon name="warning" /> Alertes stock</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['alertes_stock'] }}">0</div>
            <div class="kpi-card__trend is-down">
                <a href="{{ route('products.index', ['stock' => 'bas']) }}" class="link">voir le stock bas</a>
            </div>
        </div>

        <div class="kpi-card" style="--kpi-accent: #eab308">
            <div class="kpi-card__label"><x-icon name="star" /> Note moyenne avis</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['note_moyenne'] }}" data-count-decimals="1">0</div>
            <div class="kpi-card__trend is-flat">
                <a href="{{ route('reviews') }}" class="link">{{ $kpis['avis_count'] }} avis publiés</a>
            </div>
        </div>
    </div>

    <div class="card" data-reveal style="margin-bottom: var(--space-xl);">
        <div class="card-header">
            <h2 class="card-title">Alertes stock à traiter en priorité</h2>
            <a href="{{ route('products.index', ['stock' => 'bas']) }}" class="link">Tout voir</a>
        </div>

        @if ($lowStock->isEmpty() && $outOfStock === 0)
            <p class="text-muted text-sm">Aucune alerte — tous les produits sont correctement approvisionnés.</p>
        @else
            <div class="table-wrapper" style="border:0; border-radius:0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>SKU</th>
                            <th>Catégorie</th>
                            <th>Stock restant</th>
                            <th class="cell-actions">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lowStock as $variant)
                            <tr>
                                <td class="cell-primary">{{ $variant->product->name }} <span class="text-muted text-sm">— {{ $variant->label() }}</span></td>
                                <td class="cell-muted font-mono text-sm">{{ $variant->sku }}</td>
                                <td>{{ $variant->product->category->name }}</td>
                                <td class="cell-numeric">{{ $variant->stock_quantity }}</td>
                                <td class="cell-actions"><span class="badge badge--warning"><x-icon name="warning" /> Stock bas</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="card" data-reveal>
        <div class="card-header">
            <h2 class="card-title">Derniers produits ajoutés</h2>
            <a href="{{ route('products.index') }}" class="link">Voir le catalogue</a>
        </div>

        <div class="table-wrapper" style="border:0; border-radius:0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th class="cell-actions">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentProducts as $product)
                        <tr>
                            <td class="cell-primary">{{ $product->name }}</td>
                            <td>{{ $product->category->name }}</td>
                            <td class="cell-numeric">{{ $product->priceFormatted() }}</td>
                            <td class="cell-numeric">{{ $product->totalStock() }}</td>
                            <td class="cell-actions">
                                @if ($product->status === 'active')
                                    <span class="badge badge--success"><x-icon name="check" /> Actif</span>
                                @else
                                    <span class="badge badge--muted">Brouillon</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
