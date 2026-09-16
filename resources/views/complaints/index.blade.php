<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <h1 class="h1">Réclamations</h1>
            <p class="text-muted text-sm">{{ $complaints->total() }} réclamation(s) au total</p>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card" style="--kpi-accent: var(--color-warning)">
            <div class="kpi-card__label"><x-icon name="flag" /> Nouvelles</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['nouvelles'] }}">0</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-primary)">
            <div class="kpi-card__label"><x-icon name="clock" /> En cours</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['en_cours'] }}">0</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-accent)">
            <div class="kpi-card__label"><x-icon name="check" /> Résolues</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['resolues'] }}">0</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-primary)">
            <div class="kpi-card__label"><x-icon name="chart" /> Total</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['total'] }}">0</div>
        </div>
    </div>

    <div data-reveal>
        <form method="GET" class="table-toolbar">
            <div class="search-input" style="max-width:280px;">
                <x-icon name="search" />
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="Client ou sujet…">
            </div>

            <div class="table-filters">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Tout statut</option>
                    <option value="nouveau" @selected(request('status') === 'nouveau')>Nouvelle</option>
                    <option value="en_cours" @selected(request('status') === 'en_cours')>En cours</option>
                    <option value="resolu" @selected(request('status') === 'resolu')>Résolue</option>
                    <option value="ferme" @selected(request('status') === 'ferme')>Fermée</option>
                </select>

                <button type="submit" class="btn btn-secondary btn-sm">Filtrer</button>
                @if (request()->anyFilled(['search', 'status']))
                    <a href="{{ route('complaints') }}" class="btn btn-ghost btn-sm">Réinitialiser</a>
                @endif
            </div>
        </form>

        <div class="card" style="padding:0;">
            @forelse ($complaints as $complaint)
                <div class="review-card" x-data="{ replying: false, refunding: false }">
                    <div class="review-card__header">
                        <div>
                            <div class="review-card__author">
                                <span class="avatar" style="width:28px; height:28px; font-size:.65rem;">{{ strtoupper(substr($complaint->customer->name, 0, 1)) }}</span>
                                <span class="font-semibold">{{ $complaint->customer->name }}</span>
                                @if ($complaint->order)
                                    <span class="badge badge--muted font-mono">{{ $complaint->order->order_number }}</span>
                                @endif
                            </div>
                            <div class="review-card__meta">{{ $complaint->created_at->format('d/m/Y H:i') }}</div>
                        </div>

                        <form method="POST" action="{{ route('complaints.status', $complaint) }}">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="nouveau" @selected($complaint->status === 'nouveau')>Nouvelle</option>
                                <option value="en_cours" @selected($complaint->status === 'en_cours')>En cours</option>
                                <option value="resolu" @selected($complaint->status === 'resolu')>Résolue</option>
                                <option value="ferme" @selected($complaint->status === 'ferme')>Fermée</option>
                            </select>
                        </form>
                    </div>

                    <p class="review-card__comment"><strong>{{ $complaint->subject }}</strong></p>
                    <p class="review-card__comment">{{ $complaint->message }}</p>

                    @if ($complaint->hasReply())
                        <div class="review-card__reply">
                            <div class="review-card__reply-label">Réponse de la boutique</div>
                            {{ $complaint->admin_reply }}
                        </div>
                    @endif

                    @if ($complaint->refund)
                        <div class="review-card__reply" style="border-left-color: var(--color-accent);">
                            <div class="review-card__reply-label"><x-icon name="cash" /> Remboursement enregistré</div>
                            {{ $complaint->refund->reasonLabel() }} — {{ $complaint->refund->amountFormatted() }}
                            @if ($complaint->refund->method)
                                ({{ $complaint->refund->method }})
                            @endif
                            <div class="text-muted text-sm mt-sm">{{ $complaint->refund->processed_at->format('d/m/Y H:i') }}</div>
                        </div>
                    @endif

                    <div class="review-card__actions">
                        <button type="button" class="btn btn-secondary btn-sm" @click="replying = !replying">
                            <x-icon name="edit" /> {{ $complaint->hasReply() ? 'Modifier la réponse' : 'Répondre' }}
                        </button>

                        @if ($complaint->order && ! $complaint->refund)
                            <button type="button" class="btn btn-secondary btn-sm" @click="refunding = !refunding">
                                <x-icon name="cash" /> Rembourser
                            </button>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('complaints.reply', $complaint) }}" class="review-reply-form" x-show="replying" x-cloak>
                        @csrf
                        <input type="text" name="admin_reply" class="form-input" value="{{ $complaint->admin_reply }}" placeholder="Votre réponse au client…" required>
                        <button type="submit" class="btn btn-primary btn-sm">Envoyer</button>
                    </form>

                    @if ($complaint->order && ! $complaint->refund)
                        <form method="POST" action="{{ route('complaints.refund', $complaint) }}" class="refund-form" x-show="refunding" x-cloak>
                            @csrf

                            <div class="form-group">
                                <x-input-label value="Motif (article 9.1 des CGU)" />
                                <select name="reason" class="form-select" required>
                                    <option value="produit_defectueux">Produit défectueux</option>
                                    <option value="non_conforme">Non conforme à la description</option>
                                    <option value="erreur_commande">Erreur de commande de notre part</option>
                                    <option value="retour_reclamation">Retour accepté (réclamation)</option>
                                </select>
                            </div>

                            <div class="flex gap-md">
                                <div class="form-group" style="flex:1;">
                                    <x-input-label value="Montant remboursé" />
                                    <input type="number" name="amount" class="form-input" step="0.001" min="0.01"
                                           value="{{ number_format($complaint->order->total / 100, 3, '.', '') }}" required>
                                </div>
                                <div class="form-group" style="flex:1;">
                                    <x-input-label value="Moyen (optionnel)" />
                                    <input type="text" name="method" class="form-input" placeholder="Orange Money, Wave, espèces…">
                                </div>
                            </div>

                            <div class="form-group">
                                <x-input-label value="Note interne (optionnel)" />
                                <input type="text" name="note" class="form-input" placeholder="Référence de transfert, précisions…">
                            </div>

                            <p class="form-hint" style="margin-bottom: var(--space-md);">
                                À utiliser une fois le virement (Orange Money/Wave/espèces) réellement effectué de votre côté —
                                ceci enregistre le remboursement et prévient la cliente par e-mail, mais ne transfère rien automatiquement.
                            </p>

                            <button type="submit" class="btn btn-primary btn-sm"><x-icon name="check" /> Enregistrer le remboursement</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-muted text-sm" style="text-align:center; padding: var(--space-3xl);">
                    Aucune réclamation ne correspond à ces filtres.
                </p>
            @endforelse
        </div>

        <div class="table-wrapper" style="border:0; margin-top: var(--space-lg);">
            {{ $complaints->appends(request()->query())->links('pagination.custom') }}
        </div>
    </div>
</x-app-layout>
