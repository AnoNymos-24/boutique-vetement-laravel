<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <h1 class="h1">Marketing</h1>
            <p class="text-muted text-sm">Codes promo utilisables en vente manuelle.</p>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card" style="--kpi-accent: var(--color-accent)">
            <div class="kpi-card__label"><x-icon name="check" /> Codes actifs</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['actifs'] }}">0</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-primary)">
            <div class="kpi-card__label"><x-icon name="cart" /> Utilisations totales</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['utilisations'] }}">0</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-destructive)">
            <div class="kpi-card__label"><x-icon name="cash" /> Réduction accordée</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['reduction_totale'] / 100 }}" data-count-decimals="2" data-count-suffix=" €">0 €</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: #eab308">
            <div class="kpi-card__label"><x-icon name="star" /> Code le plus utilisé</div>
            <div class="kpi-card__value" style="font-size:1.35rem; font-family:var(--font-mono);">{{ $kpis['meilleur_code'] ?? '—' }}</div>
        </div>
    </div>

    <div class="card" data-reveal style="margin-bottom: var(--space-xl);">
        <div class="card-header">
            <h2 class="card-title">Créer un code promo</h2>
        </div>

        <form method="POST" action="{{ route('marketing.store') }}">
            @csrf
            <div class="flex gap-lg">
                <div class="form-group" style="flex:1;">
                    <x-input-label for="code">Code <span class="required-mark">*</span></x-input-label>
                    <x-text-input id="code" name="code" class="font-mono" placeholder="Ex. BIENVENUE10" required style="text-transform:uppercase;" />
                    <x-input-error :messages="$errors->get('code')" />
                </div>

                <div class="form-group" style="flex:1;">
                    <x-input-label for="type" value="Type de réduction" />
                    <select id="type" name="type" class="form-select" required>
                        <option value="pourcentage">Pourcentage (%)</option>
                        <option value="montant_fixe">Montant fixe (€)</option>
                    </select>
                </div>

                <div class="form-group" style="flex:1;">
                    <x-input-label for="value">Valeur <span class="required-mark">*</span></x-input-label>
                    <x-text-input id="value" type="number" step="0.01" min="0" name="value" required placeholder="10" />
                    <x-input-error :messages="$errors->get('value')" />
                </div>
            </div>

            <div class="flex gap-lg">
                <div class="form-group" style="flex:1;">
                    <x-input-label for="min_order_amount" value="Panier minimum (€)" />
                    <x-text-input id="min_order_amount" type="number" step="0.01" min="0" name="min_order_amount" placeholder="Optionnel" />
                </div>

                <div class="form-group" style="flex:1;">
                    <x-input-label for="max_uses" value="Nombre d'utilisations max" />
                    <x-text-input id="max_uses" type="number" min="1" name="max_uses" placeholder="Illimité" />
                </div>

                <div class="form-group" style="flex:1;">
                    <x-input-label for="expires_at" value="Date d'expiration" />
                    <x-text-input id="expires_at" type="date" name="expires_at" placeholder="Optionnel" />
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><x-icon name="plus" /> Créer le code</button>
        </form>
    </div>

    <div class="table-wrapper" data-reveal>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Réduction</th>
                    <th>Utilisations</th>
                    <th>Panier min.</th>
                    <th>Expiration</th>
                    <th>Statut</th>
                    <th class="cell-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($promoCodes as $promoCode)
                    <tr>
                        <td class="cell-primary font-mono">{{ $promoCode->code }}</td>
                        <td class="cell-numeric">{{ $promoCode->valueLabel() }}</td>
                        <td class="cell-numeric">{{ $promoCode->used_count }}{{ $promoCode->max_uses ? ' / ' . $promoCode->max_uses : '' }}</td>
                        <td class="cell-numeric text-muted">{{ $promoCode->min_order_amount ? number_format($promoCode->min_order_amount / 100, 2, ',', ' ') . ' €' : '—' }}</td>
                        <td class="cell-muted text-sm">{{ $promoCode->expires_at?->format('d/m/Y') ?? '—' }}</td>
                        <td>
                            @switch($promoCode->statusLabel())
                                @case('Actif')
                                    <span class="badge badge--success"><x-icon name="check" /> Actif</span>
                                    @break
                                @case('Expiré')
                                    <span class="badge badge--destructive">Expiré</span>
                                    @break
                                @case('Épuisé')
                                    <span class="badge badge--warning">Épuisé</span>
                                    @break
                                @default
                                    <span class="badge badge--muted">Inactif</span>
                            @endswitch
                        </td>
                        <td class="cell-actions">
                            <div class="flex gap-sm" style="justify-content:flex-end;">
                                <form method="POST" action="{{ route('marketing.toggle', $promoCode) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-secondary btn-sm">
                                        {{ $promoCode->is_active ? 'Désactiver' : 'Activer' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('marketing.destroy', $promoCode) }}"
                                      onsubmit="return confirm('Supprimer le code « {{ $promoCode->code }} » ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-icon" title="Supprimer"><x-icon name="trash" /></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-muted text-sm" style="text-align:center; padding: var(--space-3xl);">
                            Aucun code promo pour le moment.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
