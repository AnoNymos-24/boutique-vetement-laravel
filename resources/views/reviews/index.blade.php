<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <h1 class="h1">Avis</h1>
            <p class="text-muted text-sm">{{ $reviews->total() }} avis au total</p>
        </div>
        <a href="{{ route('reviews.create') }}" class="btn btn-primary">
            <x-icon name="plus" /> Ajouter un avis
        </a>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card" style="--kpi-accent: #eab308">
            <div class="kpi-card__label"><x-icon name="star" /> Note moyenne</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['note_moyenne'] }}" data-count-decimals="1">0</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-primary)">
            <div class="kpi-card__label"><x-icon name="chart" /> Total avis</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['total'] }}">0</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-warning)">
            <div class="kpi-card__label"><x-icon name="warning" /> À modérer</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['en_attente'] }}">0</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-accent)">
            <div class="kpi-card__label"><x-icon name="check" /> Taux de réponse</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['taux_reponse'] }}" data-count-suffix="%">0%</div>
        </div>
    </div>

    <div data-reveal>
        <form method="GET" class="table-toolbar">
            <div class="search-input" style="max-width:280px;">
                <x-icon name="search" />
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="Produit ou auteur…">
            </div>

            <div class="table-filters">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Tout statut</option>
                    <option value="en_attente" @selected(request('status') === 'en_attente')>À modérer</option>
                    <option value="publie" @selected(request('status') === 'publie')>Publié</option>
                    <option value="rejete" @selected(request('status') === 'rejete')>Rejeté</option>
                </select>

                <select name="rating" class="form-select" onchange="this.form.submit()">
                    <option value="">Toutes notes</option>
                    @for ($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" @selected((int) request('rating') === $i)>{{ $i }} étoile{{ $i > 1 ? 's' : '' }}</option>
                    @endfor
                </select>

                <button type="submit" class="btn btn-secondary btn-sm">Filtrer</button>
                @if (request()->anyFilled(['search', 'status', 'rating']))
                    <a href="{{ route('reviews') }}" class="btn btn-ghost btn-sm">Réinitialiser</a>
                @endif
            </div>
        </form>

        <div class="card" style="padding:0;">
            @forelse ($reviews as $review)
                <div class="review-card" x-data="{ replying: false }">
                    <div class="review-card__header">
                        <div>
                            <div class="review-card__author">
                                <span class="avatar" style="width:28px; height:28px; font-size:.65rem;">{{ strtoupper(substr($review->author_name, 0, 1)) }}</span>
                                <span class="font-semibold">{{ $review->author_name }}</span>
                                @if ($review->isVerifiedPurchase())
                                    <span class="badge badge--success"><x-icon name="check" /> Achat vérifié</span>
                                @endif
                            </div>
                            <div class="review-card__meta">{{ $review->created_at->format('d/m/Y') }}</div>
                        </div>

                        <div style="text-align:right;">
                            <x-star-rating :rating="$review->rating" />
                            <div class="mt-sm">
                                @switch($review->status)
                                    @case('publie')
                                        <span class="badge badge--success">Publié</span>
                                        @break
                                    @case('rejete')
                                        <span class="badge badge--destructive">Rejeté</span>
                                        @break
                                    @default
                                        <span class="badge badge--warning"><x-icon name="warning" /> À modérer</span>
                                @endswitch
                            </div>
                        </div>
                    </div>

                    <div class="review-card__product">
                        @if ($review->product->images->first())
                            <img src="{{ $review->product->images->first()->url() }}" alt="" class="product-thumb">
                        @else
                            <span class="product-thumb product-thumb--empty"><x-icon name="shirt" /></span>
                        @endif
                        {{ $review->product->name }}
                    </div>

                    <p class="review-card__comment">{{ $review->comment }}</p>

                    @if ($review->hasReply())
                        <div class="review-card__reply">
                            <div class="review-card__reply-label">Réponse de la boutique</div>
                            {{ $review->admin_reply }}
                        </div>
                    @endif

                    <div class="review-card__actions">
                        @if ($review->status === 'en_attente')
                            <form method="POST" action="{{ route('reviews.approve', $review) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-primary btn-sm"><x-icon name="check" /> Publier</button>
                            </form>
                            <form method="POST" action="{{ route('reviews.reject', $review) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-secondary btn-sm">Rejeter</button>
                            </form>
                        @endif

                        @if ($review->status === 'publie' && ! $review->hasReply())
                            <button type="button" class="btn btn-secondary btn-sm" @click="replying = !replying">
                                <x-icon name="edit" /> Répondre
                            </button>
                        @endif

                        <form method="POST" action="{{ route('reviews.destroy', $review) }}"
                              onsubmit="return confirm('Supprimer cet avis définitivement ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-ghost btn-icon" title="Supprimer"><x-icon name="trash" /></button>
                        </form>
                    </div>

                    <form method="POST" action="{{ route('reviews.reply', $review) }}" class="review-reply-form" x-show="replying" x-cloak>
                        @csrf
                        <input type="text" name="admin_reply" class="form-input" placeholder="Merci pour votre retour…" required>
                        <button type="submit" class="btn btn-primary btn-sm">Envoyer</button>
                    </form>
                </div>
            @empty
                <p class="text-muted text-sm" style="text-align:center; padding: var(--space-3xl);">
                    Aucun avis ne correspond à ces filtres.
                </p>
            @endforelse
        </div>

        <div class="table-wrapper" style="border:0; margin-top: var(--space-lg);">
            {{ $reviews->appends(request()->query())->links('pagination.custom') }}
        </div>
    </div>
</x-app-layout>
