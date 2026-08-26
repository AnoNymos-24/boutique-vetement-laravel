<x-shop-layout>
    <div class="shop-container" style="padding: var(--shop-space-lg) var(--shop-space-lg) var(--shop-space-3xl); max-width: 960px;">
        <div class="checkout-steps" data-reveal>
            <div class="checkout-steps__track">
                <div class="checkout-steps__track-fill" style="width: 50%"></div>
            </div>
            <div class="checkout-step is-done">
                <span class="checkout-step__index"><x-icon name="check" style="width:14px;height:14px;" /></span>
                <span class="checkout-step__label">Panier</span>
            </div>
            <div class="checkout-step is-active">
                <span class="checkout-step__index">2</span>
                <span class="checkout-step__label">Livraison &amp; paiement</span>
            </div>
            <div class="checkout-step">
                <span class="checkout-step__index">3</span>
                <span class="checkout-step__label">Confirmation</span>
            </div>
        </div>

        <h1 class="shop-h1" style="font-size: 2rem; margin-bottom: var(--shop-space-xl);" data-reveal>Finaliser la commande</h1>

        @if ($errors->any())
            <div class="shop-status-banner shop-status-banner--error" data-reveal>
                {{ $errors->first() }}
            </div>
        @endif

        <div class="cart-layout" data-reveal>
            <form method="POST" action="{{ route('shop.checkout.store') }}">
                @csrf

                <h3 class="shop-h3" style="margin-bottom: var(--shop-space-md);">Coordonnées</h3>

                <div class="shop-field">
                    <label class="shop-label" for="customer_name">Nom complet</label>
                    <input type="text" id="customer_name" name="customer_name" class="shop-input" required
                           value="{{ old('customer_name', $customer?->name) }}">
                    <x-input-error :messages="$errors->get('customer_name')" />
                </div>

                <div class="shop-field">
                    <label class="shop-label" for="customer_phone">Téléphone</label>
                    <input type="tel" id="customer_phone" name="customer_phone" class="shop-input" required placeholder="07…" inputmode="tel"
                           value="{{ old('customer_phone', $customer?->phone) }}">
                    <x-input-error :messages="$errors->get('customer_phone')" />
                </div>

                <div class="shop-field" x-data="{ address: @js(old('customer_address', $addresses->firstWhere('is_default', true)?->full_address ?? $addresses->first()?->full_address ?? $customer?->address ?? '')) }">
                    <label class="shop-label" for="customer_address">Adresse de livraison</label>

                    @if ($addresses->isNotEmpty())
                        <div class="shop-flex shop-gap-sm" style="flex-wrap:wrap; margin-bottom: var(--shop-space-sm);">
                            @foreach ($addresses as $savedAddress)
                                <button type="button" class="filter-swatch" :class="{ 'is-active': address === @js($savedAddress->full_address) }"
                                        @click="address = @js($savedAddress->full_address)">
                                    {{ $savedAddress->label }}
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <textarea id="customer_address" name="customer_address" class="shop-textarea" rows="2" required x-model="address"></textarea>
                    <x-input-error :messages="$errors->get('customer_address')" />
                </div>

                <h3 class="shop-h3" style="margin: var(--shop-space-xl) 0 var(--shop-space-md);">Paiement</h3>

                <div class="shop-field">
                    <label class="payment-method-card" style="margin-bottom: var(--shop-space-sm);">
                        <input type="radio" name="payment_method" value="orange_money" {{ old('payment_method', 'orange_money') === 'orange_money' ? 'checked' : '' }}>
                        <span class="payment-method-card__icon payment-method-card__icon--orange-money"><x-icon name="cash" /></span> Orange Money
                    </label>
                    <label class="payment-method-card">
                        <input type="radio" name="payment_method" value="wave" {{ old('payment_method') === 'wave' ? 'checked' : '' }}>
                        <span class="payment-method-card__icon payment-method-card__icon--wave"><x-icon name="cash" /></span> Wave
                    </label>
                    <x-input-error :messages="$errors->get('payment_method')" />
                </div>

                <div class="shop-field">
                    <label class="shop-label" for="payment_reference">6 derniers chiffres de la référence de paiement</label>
                    <input type="text" id="payment_reference" name="payment_reference" class="shop-input" style="font-family: var(--shop-font-mono);" inputmode="numeric" maxlength="6" required
                           placeholder="123456" value="{{ old('payment_reference') }}">
                    <p class="shop-hint">Effectuez le paiement via l'app Orange Money ou Wave, puis saisissez les 6 derniers chiffres de la référence reçue par SMS.</p>
                    <x-input-error :messages="$errors->get('payment_reference')" />
                </div>

                <button type="submit" class="shop-btn shop-btn--primary shop-btn--full" style="margin-top: var(--shop-space-lg);">
                    Confirmer la commande <x-icon name="arrow-right" />
                </button>
            </form>

            <div class="cart-summary">
                <h3 class="shop-h3" style="margin-bottom: var(--shop-space-md);">Votre commande</h3>
                @foreach ($items as $item)
                    <div class="shop-info-row">
                        <span class="shop-info-row__label">{{ $item['product']->name }} ({{ $item['variant']->label() }}) × {{ $item['quantity'] }}</span>
                        <span class="cart-summary__value">{{ number_format($item['subtotal'] / 100, 3, ',', ' ') }} DT</span>
                    </div>
                @endforeach

                <div class="cart-summary__row cart-summary__row--total">
                    <span>Total</span>
                    <span class="cart-summary__value">{{ number_format($subtotal / 100, 3, ',', ' ') }} DT</span>
                </div>
            </div>
        </div>
    </div>
</x-shop-layout>
