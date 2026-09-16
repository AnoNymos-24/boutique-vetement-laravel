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

        <h1 class="shop-h1" style="font-size: clamp(1.375rem, 5vw, 2rem); margin-bottom: var(--shop-space-xl);" data-reveal>Finaliser la commande</h1>

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

                @if (empty($paymentMethods))
                    <div class="shop-status-banner shop-status-banner--error">
                        Aucun moyen de paiement n'est disponible pour le moment. Merci de nous contacter pour finaliser votre commande.
                    </div>
                @else
                    <div class="shop-field" x-data="{ method: @js(old('payment_method', array_key_first($paymentMethods))) }">
                        @foreach ($paymentMethods as $key => $meta)
                            <label class="payment-method-card" style="margin-bottom: var(--shop-space-sm);">
                                <input type="radio" name="payment_method" value="{{ $key }}" x-model="method" {{ old('payment_method', array_key_first($paymentMethods)) === $key ? 'checked' : '' }}>
                                <span class="payment-method-card__icon payment-method-card__icon--{{ str_replace('_', '-', $key) }}">
                                    <x-icon :name="$key === 'especes' ? 'truck' : 'cash'" />
                                </span>
                                {{ $meta['label'] }}
                            </label>
                        @endforeach
                        <x-input-error :messages="$errors->get('payment_method')" />

                        @if (isset($paymentMethods['orange_money']))
                            <div class="payment-instructions" x-show="method === 'orange_money'" x-cloak>
                                <p class="payment-instructions__title"><x-icon name="cash" /> Comment payer avec Orange Money</p>
                                <ol class="payment-instructions__steps">
                                    <li><span class="payment-instructions__index">1</span> <span class="payment-instructions__text">Composez <strong>#144#</strong> ou ouvrez l'application Orange Money.</span></li>
                                    <li><span class="payment-instructions__index">2</span> <span class="payment-instructions__text">Choisissez « Transfert d'argent » et envoyez le montant exact au numéro ci-dessous.</span></li>
                                </ol>
                                <div class="payment-instructions__callout">
                                    <span class="payment-instructions__callout-label">Numéro Orange Money</span>
                                    <span class="payment-instructions__callout-value">{{ $setting->paymentMethodNumber('orange_money') }}</span>
                                </div>
                                <ol class="payment-instructions__steps" start="3">
                                    <li><span class="payment-instructions__index">3</span> <span class="payment-instructions__text">Vous recevrez un SMS avec une référence de transaction.</span></li>
                                    <li><span class="payment-instructions__index">4</span> <span class="payment-instructions__text">Saisissez ci-dessous les <strong>6 derniers chiffres</strong> de cette référence.</span></li>
                                </ol>
                            </div>
                        @endif

                        @if (isset($paymentMethods['wave']))
                            <div class="payment-instructions" x-show="method === 'wave'" x-cloak>
                                <p class="payment-instructions__title"><x-icon name="cash" /> Comment payer avec Wave</p>
                                <ol class="payment-instructions__steps">
                                    <li><span class="payment-instructions__index">1</span> <span class="payment-instructions__text">Ouvrez l'application Wave et appuyez sur « Envoyer de l'argent ».</span></li>
                                    <li><span class="payment-instructions__index">2</span> <span class="payment-instructions__text">Entrez le montant exact de votre commande au numéro ci-dessous.</span></li>
                                </ol>
                                <div class="payment-instructions__callout">
                                    <span class="payment-instructions__callout-label">Numéro Wave</span>
                                    <span class="payment-instructions__callout-value">{{ $setting->paymentMethodNumber('wave') }}</span>
                                </div>
                                <ol class="payment-instructions__steps" start="3">
                                    <li><span class="payment-instructions__index">3</span> <span class="payment-instructions__text">Confirmez l'envoi — vous recevrez une référence de transaction.</span></li>
                                    <li><span class="payment-instructions__index">4</span> <span class="payment-instructions__text">Saisissez ci-dessous les <strong>6 derniers chiffres</strong> de cette référence.</span></li>
                                </ol>
                            </div>
                        @endif

                        @if (isset($paymentMethods['especes']))
                            <div class="payment-instructions" x-show="method === 'especes'" x-cloak>
                                <p class="payment-instructions__title"><x-icon name="truck" /> Paiement à la livraison</p>
                                <ol class="payment-instructions__steps">
                                    <li><span class="payment-instructions__index">1</span> <span class="payment-instructions__text">Votre commande est confirmée sans paiement en ligne.</span></li>
                                    <li><span class="payment-instructions__index">2</span> <span class="payment-instructions__text">Préparez le montant exact en espèces.</span></li>
                                    <li><span class="payment-instructions__index">3</span> <span class="payment-instructions__text">Le livreur encaissera le paiement à la remise de votre colis.</span></li>
                                </ol>
                            </div>
                        @endif

                        <div class="shop-field" x-show="method === 'orange_money' || method === 'wave'" x-cloak style="margin-top:0;">
                            <label class="shop-label" for="payment_reference">6 derniers chiffres de la référence de paiement</label>
                            <input type="text" id="payment_reference" name="payment_reference" class="shop-input" style="font-family: var(--shop-font-mono);" inputmode="numeric" maxlength="6"
                                   :required="method === 'orange_money' || method === 'wave'"
                                   placeholder="123456" value="{{ old('payment_reference') }}">
                            <x-input-error :messages="$errors->get('payment_reference')" />
                        </div>
                    </div>

                    <button type="submit" class="shop-btn shop-btn--primary shop-btn--full" style="margin-top: var(--shop-space-lg);">
                        Confirmer la commande
                    </button>
                @endif
            </form>

            <div class="cart-summary">
                <h3 class="shop-h3" style="margin-bottom: var(--shop-space-md);">Votre commande</h3>
                @foreach ($items as $item)
                    <div class="shop-info-row">
                        <span class="shop-info-row__label">{{ $item['product']->name }} ({{ $item['variant']->label() }}) × {{ $item['quantity'] }}</span>
                        <span class="cart-summary__value">{{ \App\Models\StoreSetting::current()->formatMoney($item['subtotal']) }}</span>
                    </div>
                @endforeach

                <div class="cart-summary__row cart-summary__row--total">
                    <span>Total</span>
                    <span class="cart-summary__value">{{ \App\Models\StoreSetting::current()->formatMoney($subtotal) }}</span>
                </div>
            </div>
        </div>
    </div>
</x-shop-layout>
