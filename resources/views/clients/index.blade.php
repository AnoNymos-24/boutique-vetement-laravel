<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <h1 class="h1">Clients</h1>
            <p class="text-muted text-sm">{{ $customers->total() }} client(s) au total</p>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card" style="--kpi-accent: var(--color-primary)">
            <div class="kpi-card__label"><x-icon name="users" /> Total clients</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['total'] }}">0</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-accent)">
            <div class="kpi-card__label"><x-icon name="plus" /> Nouveaux ce mois</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['nouveaux_ce_mois'] }}">0</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: #eab308">
            <div class="kpi-card__label"><x-icon name="star" /> Clients VIP</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['vip'] }}">0</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-warning)">
            <div class="kpi-card__label"><x-icon name="cash" /> Panier moyen</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['panier_moyen'] / 100 }}" data-count-decimals="{{ $currency['decimals'] }}" data-count-suffix=" {{ $currency['symbol'] }}">{{ number_format(0, $currency['decimals']) }} {{ $currency['symbol'] }}</div>
        </div>
    </div>

    <div data-reveal>
        <form method="GET" class="table-toolbar">
            <div class="search-input" style="max-width:280px;">
                <x-icon name="search" />
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="Nom ou téléphone…">
            </div>

            <div class="table-filters">
                <select name="segment" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous segments</option>
                    <option value="nouveau" @selected(request('segment') === 'nouveau')>Nouveau</option>
                    <option value="regulier" @selected(request('segment') === 'regulier')>Régulier</option>
                    <option value="vip" @selected(request('segment') === 'vip')>VIP</option>
                </select>

                <button type="submit" class="btn btn-secondary btn-sm">Filtrer</button>
                @if (request()->anyFilled(['search', 'segment']))
                    <a href="{{ route('clients') }}" class="btn btn-ghost btn-sm">Réinitialiser</a>
                @endif
            </div>
        </form>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Téléphone</th>
                        <th>Segment</th>
                        <th>Commandes</th>
                        <th>Total dépensé</th>
                        <th class="cell-actions"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td class="cell-primary">
                                <div class="flex items-center gap-md">
                                    <span class="avatar" style="width:30px; height:30px; font-size:.7rem;">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                                    {{ $customer->name }}
                                </div>
                            </td>
                            <td class="cell-muted font-mono text-sm">{{ $customer->phone }}</td>
                            <td>
                                @switch($customer->segment())
                                    @case('vip')
                                        <span class="badge badge--warning"><x-icon name="star" /> VIP</span>
                                        @break
                                    @case('regulier')
                                        <span class="badge badge--success">Régulier</span>
                                        @break
                                    @default
                                        <span class="badge badge--muted">Nouveau</span>
                                @endswitch
                            </td>
                            <td class="cell-numeric">{{ $customer->orders_count }}</td>
                            <td class="cell-numeric font-semibold">{{ \App\Models\StoreSetting::current()->formatMoney($customer->total_spent ?? 0) }}</td>
                            <td class="cell-actions">
                                <a href="{{ route('clients.show', $customer) }}" class="btn btn-secondary btn-sm">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted text-sm" style="text-align:center; padding: var(--space-3xl);">
                                Aucun client ne correspond à ces filtres.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $customers->appends(request()->query())->links('pagination.custom') }}
        </div>
    </div>
</x-app-layout>
