<x-shop-layout>
    <div class="shop-container" style="padding: var(--shop-space-lg) var(--shop-space-lg) var(--shop-space-3xl);">
        <x-shop.account-layout active="complaints">
            @if (session('status'))
                <div class="shop-status-banner">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="shop-status-banner shop-status-banner--error">{{ session('error') }}</div>
            @endif

            <div style="margin-bottom: var(--shop-space-lg);" data-reveal>
                <p class="shop-eyebrow">Support</p>
                <p class="shop-text-muted">Un problème avec une commande ou votre compte ? Décrivez-le, notre équipe vous répond ici.</p>
            </div>

            <div class="account-card shop-card" data-reveal>
                <h3 class="shop-h3" style="margin-bottom: var(--shop-space-md);">Nouvelle réclamation</h3>

                <form method="POST" action="{{ route('customer.complaints.store') }}">
                    @csrf

                    @if ($orders->isNotEmpty())
                        <div class="shop-field">
                            <label class="shop-label" for="order_id">Commande concernée (optionnel)</label>
                            <select id="order_id" name="order_id" class="shop-select">
                                <option value="">Non liée à une commande précise</option>
                                @foreach ($orders as $order)
                                    <option value="{{ $order->id }}">{{ $order->order_number }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="shop-field">
                        <label class="shop-label" for="subject">Sujet</label>
                        <input type="text" id="subject" name="subject" class="shop-input" required maxlength="255" placeholder="Ex. Article manquant dans ma commande">
                        <x-input-error :messages="$errors->get('subject')" />
                    </div>

                    <div class="shop-field">
                        <label class="shop-label" for="message">Votre message</label>
                        <textarea id="message" name="message" class="shop-textarea" rows="4" required maxlength="2000"></textarea>
                        <x-input-error :messages="$errors->get('message')" />
                    </div>

                    <button type="submit" class="shop-btn shop-btn--primary shop-btn--sm">Envoyer la réclamation</button>
                </form>
            </div>

            <div class="account-card shop-card" data-reveal>
                <h3 class="shop-h3" style="margin-bottom: var(--shop-space-md);">Mes réclamations</h3>

                @forelse ($complaints as $complaint)
                    <div style="padding: var(--shop-space-md) 0; border-bottom: 1px solid var(--shop-color-border);">
                        <div class="shop-flex shop-justify-between shop-items-center">
                            <strong>{{ $complaint->subject }}</strong>
                            <span class="shop-badge {{ $complaint->status === 'resolu' ? 'shop-badge--success' : ($complaint->status === 'ferme' ? 'shop-badge--muted' : 'shop-badge--warning') }}">
                                {{ $complaint->statusLabel() }}
                            </span>
                        </div>
                        <p class="shop-text-muted" style="font-size:0.8125rem; margin-top:2px;">{{ $complaint->created_at->format('d/m/Y') }}</p>
                        <p style="margin-top: var(--shop-space-sm);">{{ $complaint->message }}</p>

                        @if ($complaint->hasReply())
                            <div style="margin-top: var(--shop-space-sm); padding: var(--shop-space-sm) var(--shop-space-md); background: var(--shop-color-muted); border-radius: var(--shop-radius-md);">
                                <div class="shop-text-muted" style="font-size:0.75rem; font-weight:600; margin-bottom:2px;">Réponse de la boutique</div>
                                {{ $complaint->admin_reply }}
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="shop-text-muted">Vous n'avez encore déposé aucune réclamation.</p>
                @endforelse
            </div>
        </x-shop.account-layout>
    </div>
</x-shop-layout>
