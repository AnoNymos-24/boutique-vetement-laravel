<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <h1 class="h1">Messages</h1>
            <p class="text-muted text-sm">{{ $messages->total() }} message(s) reçu(s) via le formulaire de contact</p>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card" style="--kpi-accent: var(--color-warning)">
            <div class="kpi-card__label"><x-icon name="mail" /> Nouveaux</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['nouveaux'] }}">0</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--color-accent)">
            <div class="kpi-card__label"><x-icon name="check" /> Traités</div>
            <div class="kpi-card__value" data-count-to="{{ $kpis['traites'] }}">0</div>
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
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="Nom ou sujet…">
            </div>

            <div class="table-filters">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Tout statut</option>
                    <option value="nouveau" @selected(request('status') === 'nouveau')>Nouveau</option>
                    <option value="lu" @selected(request('status') === 'lu')>Lu</option>
                    <option value="traite" @selected(request('status') === 'traite')>Traité</option>
                </select>

                <button type="submit" class="btn btn-secondary btn-sm">Filtrer</button>
                @if (request()->anyFilled(['search', 'status']))
                    <a href="{{ route('contact-messages') }}" class="btn btn-ghost btn-sm">Réinitialiser</a>
                @endif
            </div>
        </form>

        <div class="card" style="padding:0;">
            @forelse ($messages as $message)
                <div class="review-card" x-data="{ replying: false }">
                    <div class="review-card__header">
                        <div>
                            <div class="review-card__author">
                                <span class="avatar" style="width:28px; height:28px; font-size:.65rem;">{{ strtoupper(substr($message->name, 0, 1)) }}</span>
                                <span class="font-semibold">{{ $message->name }}</span>
                                <span class="text-muted text-sm">{{ $message->email }}</span>
                                @if ($message->phone)
                                    <span class="text-muted text-sm">· {{ $message->phone }}</span>
                                @endif
                            </div>
                            <div class="review-card__meta">{{ $message->created_at->format('d/m/Y H:i') }}</div>
                        </div>

                        <form method="POST" action="{{ route('contact-messages.status', $message) }}">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="nouveau" @selected($message->status === 'nouveau')>Nouveau</option>
                                <option value="lu" @selected($message->status === 'lu')>Lu</option>
                                <option value="traite" @selected($message->status === 'traite')>Traité</option>
                            </select>
                        </form>
                    </div>

                    <p class="review-card__comment"><strong>{{ $message->subject }}</strong></p>
                    <p class="review-card__comment">{{ $message->body }}</p>

                    @if ($message->hasReply())
                        <div class="review-card__reply">
                            <div class="review-card__reply-label">Réponse envoyée</div>
                            {{ $message->admin_reply }}
                        </div>
                    @endif

                    <div class="review-card__actions">
                        <button type="button" class="btn btn-secondary btn-sm" @click="replying = !replying">
                            <x-icon name="edit" /> {{ $message->hasReply() ? 'Modifier la réponse' : 'Répondre' }}
                        </button>
                    </div>

                    <form method="POST" action="{{ route('contact-messages.reply', $message) }}" class="review-reply-form" x-show="replying" x-cloak>
                        @csrf
                        <input type="text" name="admin_reply" class="form-input" value="{{ $message->admin_reply }}" placeholder="Votre réponse…" required>
                        <button type="submit" class="btn btn-primary btn-sm">Envoyer</button>
                    </form>
                </div>
            @empty
                <p class="text-muted text-sm" style="text-align:center; padding: var(--space-3xl);">
                    Aucun message ne correspond à ces filtres.
                </p>
            @endforelse
        </div>

        <div class="table-wrapper" style="border:0; margin-top: var(--space-lg);">
            {{ $messages->appends(request()->query())->links('pagination.custom') }}
        </div>
    </div>
</x-app-layout>
