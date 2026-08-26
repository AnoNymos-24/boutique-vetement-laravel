<x-shop-layout>
    <div class="shop-container" style="padding: var(--shop-space-lg) var(--shop-space-lg) var(--shop-space-3xl);">
        <x-shop.account-layout active="reviews">
            @if (session('status'))
                <div class="shop-status-banner">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="shop-status-banner shop-status-banner--error">{{ session('error') }}</div>
            @endif

            <div style="margin-bottom: var(--shop-space-lg);" data-reveal>
                <p class="shop-eyebrow">Retours</p>
                <p class="shop-text-muted">Partagez votre expérience sur les articles achetés.</p>
            </div>

            @if ($eligibleItems->isNotEmpty())
                <div class="account-card shop-card" data-reveal>
                    <h3 class="shop-h3" style="margin-bottom: var(--shop-space-md);">Produits à évaluer</h3>

                    @foreach ($eligibleItems as $item)
                        <div x-data="accordion()" style="border-bottom: 1px solid var(--shop-color-border); padding-bottom: var(--shop-space-md); margin-bottom: var(--shop-space-md);">
                            <div class="shop-flex shop-justify-between shop-items-center">
                                <span style="font-weight:600;">{{ $item->product_name }}</span>
                                <button type="button" class="shop-btn shop-btn--secondary shop-btn--sm" @click="open = !open" x-text="open ? 'Annuler' : 'Laisser un avis'"></button>
                            </div>

                            <form method="POST" action="{{ route('customer.reviews.store') }}" x-show="open" x-cloak style="margin-top: var(--shop-space-md);">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item->product_id }}">

                                <div class="shop-field" x-data="{ rating: 5 }">
                                    <label class="shop-label">Note</label>
                                    <input type="hidden" name="rating" x-model="rating">
                                    <div class="shop-flex shop-gap-sm">
                                        <template x-for="n in 5" :key="n">
                                            <button type="button" @click="rating = n" style="cursor:pointer;">
                                                <x-icon name="star" x-bind:style="'width:22px;height:22px;' + (n <= rating ? 'color:var(--shop-color-accent);fill:var(--shop-color-accent);' : 'color:var(--shop-color-border);')" />
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <div class="shop-field">
                                    <label class="shop-label" for="comment-{{ $item->product_id }}">Votre avis</label>
                                    <textarea id="comment-{{ $item->product_id }}" name="comment" class="shop-textarea" rows="3" required></textarea>
                                </div>

                                <button type="submit" class="shop-btn shop-btn--primary shop-btn--sm">Envoyer l'avis</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="account-card shop-card" data-reveal>
                <h3 class="shop-h3" style="margin-bottom: var(--shop-space-md);">Mes avis publiés</h3>

                @forelse ($myReviews as $review)
                    <div style="padding: var(--shop-space-md) 0; border-bottom: 1px solid var(--shop-color-border);">
                        <div class="shop-flex shop-justify-between shop-items-center">
                            <strong>{{ $review->product->name }}</strong>
                            <span class="shop-badge {{ $review->status === 'publie' ? 'shop-badge--success' : ($review->status === 'rejete' ? '' : 'shop-badge--warning') }}">
                                {{ $review->statusLabel() }}
                            </span>
                        </div>
                        <x-star-rating :rating="$review->rating" />
                        <p style="margin-top: var(--shop-space-sm);">{{ $review->comment }}</p>
                    </div>
                @empty
                    <p class="shop-text-muted">Vous n'avez pas encore laissé d'avis.</p>
                @endforelse
            </div>
        </x-shop.account-layout>
    </div>
</x-shop-layout>
