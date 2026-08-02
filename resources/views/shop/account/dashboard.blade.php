<x-shop-layout>
    <div class="shop-container" style="padding: var(--shop-space-lg) var(--shop-space-lg) var(--shop-space-3xl);">
        <x-shop.account-layout active="dashboard">
            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap: var(--shop-space-lg); margin-bottom: var(--shop-space-lg);">
                <div class="account-card" style="margin-bottom:0; text-align:center;">
                    <div class="pdp-buybox__price" style="font-size:1.75rem;">{{ $ordersCount }}</div>
                    <div class="shop-text-muted" style="font-size:0.8125rem;">Commande(s)</div>
                </div>
                <div class="account-card" style="margin-bottom:0; text-align:center;">
                    <div class="pdp-buybox__price" style="font-size:1.75rem;">{{ $wishlistCount }}</div>
                    <div class="shop-text-muted" style="font-size:0.8125rem;">Favori(s)</div>
                </div>
                <div class="account-card" style="margin-bottom:0; text-align:center;">
                    <div class="pdp-buybox__price" style="font-size:1.75rem;">{{ $customer->addresses()->count() }}</div>
                    <div class="shop-text-muted" style="font-size:0.8125rem;">Adresse(s)</div>
                </div>
            </div>

            <div class="account-card">
                <div class="shop-flex shop-justify-between shop-items-center" style="margin-bottom: var(--shop-space-md);">
                    <h3 class="shop-h3">Commandes récentes</h3>
                    <a href="{{ route('customer.account.orders') }}" class="shop-btn shop-btn--secondary shop-btn--sm">Tout voir</a>
                </div>

                @forelse ($recentOrders as $order)
                    <div class="account-order-row">
                        <div>
                            <div class="account-order-row__number">{{ $order->order_number }}</div>
                            <div class="shop-text-muted" style="font-size:0.8125rem;">{{ $order->created_at->format('d/m/Y') }} — {{ $order->statusLabel() }}</div>
                        </div>
                        <div class="shop-flex shop-gap-md shop-items-center">
                            <span class="cart-summary__value">{{ $order->totalFormatted() }}</span>
                            <a href="{{ route('customer.account.orders.show', $order) }}" class="shop-btn shop-btn--ghost shop-btn--sm">Détails</a>
                        </div>
                    </div>
                @empty
                    <p class="shop-text-muted">Aucune commande pour le moment.</p>
                @endforelse
            </div>

            <div class="account-card">
                <h3 class="shop-h3" style="margin-bottom: var(--shop-space-md);">Mes informations</h3>

                @if (session('status'))
                    <div class="shop-status-banner">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('customer.account.profile') }}">
                    @csrf
                    @method('PATCH')

                    <div class="shop-field">
                        <label class="shop-label" for="name">Nom</label>
                        <input type="text" id="name" name="name" class="shop-input" value="{{ old('name', $customer->name) }}" required>
                        <x-input-error :messages="$errors->get('name')" />
                    </div>

                    <div class="shop-field">
                        <label class="shop-label" for="email">Email</label>
                        <input type="email" id="email" name="email" class="shop-input" value="{{ old('email', $customer->email) }}" required>
                        <x-input-error :messages="$errors->get('email')" />
                    </div>

                    <div class="shop-field">
                        <label class="shop-label" for="phone">Téléphone</label>
                        <input type="text" id="phone" name="phone" class="shop-input" value="{{ old('phone', $customer->phone) }}" required>
                        <x-input-error :messages="$errors->get('phone')" />
                    </div>

                    <button type="submit" class="shop-btn shop-btn--primary">Enregistrer</button>
                </form>
            </div>
        </x-shop.account-layout>
    </div>
</x-shop-layout>
