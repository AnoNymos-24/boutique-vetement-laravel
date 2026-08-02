<!DOCTYPE html>
<html lang="fr" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Vente manuelle — {{ $storeName }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@500;600;700&family=Fira+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    </head>
    <body>
        <div class="pos-shell" x-data="posRegister(@js($products->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'price' => $p->price,
            'image' => $p->images->first()?->url(),
            'variants' => $p->variants->map(fn ($v) => [
                'id' => $v->id,
                'label' => $v->label(),
                'sku' => $v->sku,
                'stock' => $v->stock_quantity,
            ]),
        ])), @js(route('pos.check-promo')))">
            <header class="pos-topbar">
                <a href="{{ route('dashboard') }}" class="pos-topbar__back">
                    <x-icon name="chevron-left" /> Retour à l'admin
                </a>
                <div class="pos-topbar__title"><x-icon name="cash" /> Vente manuelle</div>
                <div style="margin-left:auto; font-size:.8125rem; color:#94a3b8;">{{ auth()->user()->name }}</div>
            </header>

            @if (session('status'))
                <div class="status-banner" style="margin: var(--space-lg) var(--space-2xl) 0;" data-reveal>{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="status-banner status-banner--error" style="margin: var(--space-lg) var(--space-2xl) 0;" data-reveal>{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="status-banner status-banner--error" style="margin: var(--space-lg) var(--space-2xl) 0;">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="pos-body">
                <div class="pos-catalog">
                    <div class="search-input pos-catalog__search">
                        <x-icon name="search" />
                        <input type="text" class="form-input" placeholder="Rechercher un produit ou SKU…" x-model="search">
                    </div>

                    <div class="pos-product-grid">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <button type="button" class="pos-product" @click="pickVariant(product)">
                                <template x-if="product.image">
                                    <img :src="product.image" class="pos-product__image" alt="">
                                </template>
                                <template x-if="!product.image">
                                    <div class="pos-product__image pos-product__image--empty">
                                        <x-icon name="shirt" />
                                    </div>
                                </template>
                                <div class="pos-product__name" x-text="product.name"></div>
                                <div class="pos-product__footer">
                                    <span class="pos-product__price" x-text="priceFormatted(product.price)"></span>
                                    <span class="pos-product__stock" x-text="totalStock(product) + ' en stock'"></span>
                                </div>
                            </button>
                        </template>

                        <template x-if="filteredProducts.length === 0">
                            <p class="text-muted text-sm">Aucun produit ne correspond à cette recherche.</p>
                        </template>
                    </div>
                </div>

                <x-modal name="variant-pick">
                    <div style="padding: var(--space-2xl);" x-show="pickingProduct">
                        <h2 class="h3" style="margin-bottom: var(--space-lg);" x-text="pickingProduct?.name"></h2>
                        <div class="pill-checkbox-group">
                            <template x-for="variant in pickingProduct?.variants ?? []" :key="variant.id">
                                <button type="button" class="pill-checkbox" :disabled="variant.stock === 0" @click="chooseVariant(variant)">
                                    <span x-text="variant.label"></span>
                                    <span class="text-muted" x-text="'(' + variant.stock + ')'"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </x-modal>

                <form method="POST" action="{{ route('pos.store') }}" class="pos-cart" @submit="submitting = true">
                    @csrf

                    <div class="pos-cart__header">
                        <span>Panier</span>
                        <span x-text="cart.length + ' article(s)'"></span>
                    </div>

                    <div class="pos-cart__items">
                        <template x-if="cart.length === 0">
                            <p class="pos-cart__empty">Touchez un produit pour l'ajouter</p>
                        </template>

                        <template x-for="item in cart" :key="item.id">
                            <div class="pos-cart-item">
                                <input type="hidden" :name="'items[' + item.id + '][product_variant_id]'" :value="item.id">
                                <input type="hidden" :name="'items[' + item.id + '][quantity]'" :value="item.qty">

                                <div class="pos-cart-item__info">
                                    <div class="pos-cart-item__name" x-text="item.name"></div>
                                    <div class="pos-cart-item__variant text-muted text-sm" x-text="item.variantLabel"></div>
                                    <div class="pos-cart-item__price" x-text="priceFormatted(item.price)"></div>
                                </div>

                                <div class="pos-qty-stepper">
                                    <button type="button" @click="decrement(item)">−</button>
                                    <span class="pos-qty-stepper__value" x-text="item.qty"></span>
                                    <button type="button" @click="increment(item)" :disabled="item.qty >= item.stock">+</button>
                                </div>

                                <button type="button" class="pos-cart-item__remove" @click="removeFromCart(item.id)" title="Retirer">
                                    <x-icon name="x" />
                                </button>
                            </div>
                        </template>
                    </div>

                    <div class="pos-cart__footer">
                        <div class="form-group">
                            <x-input-label for="customer_name" value="Client (optionnel)" />
                            <x-text-input id="customer_name" name="customer_name" placeholder="Nom du client" />
                        </div>

                        <div class="form-group">
                            <x-input-label for="customer_phone" value="Téléphone (optionnel)" />
                            <x-text-input id="customer_phone" name="customer_phone" placeholder="07…" />
                        </div>

                        <x-input-label value="Moyen de paiement" />
                        <div class="pos-payment-methods">
                            <label class="pos-payment-method">
                                <input type="radio" name="payment_method" value="especes" x-model="paymentMethod">
                                <x-icon name="cash" />
                                Espèces
                            </label>
                            <label class="pos-payment-method">
                                <input type="radio" name="payment_method" value="orange_money" x-model="paymentMethod">
                                <x-icon name="cash" />
                                Orange Money
                            </label>
                            <label class="pos-payment-method">
                                <input type="radio" name="payment_method" value="wave" x-model="paymentMethod">
                                <x-icon name="cash" />
                                Wave
                            </label>
                        </div>

                        <div class="form-group">
                            <x-input-label for="promo_input" value="Code promo (optionnel)" />
                            <div class="flex gap-sm">
                                <input type="text" id="promo_input" class="form-input font-mono" style="text-transform:uppercase;"
                                       x-model="promoInput" placeholder="BIENVENUE10" :disabled="appliedPromo !== null">
                                <template x-if="!appliedPromo">
                                    <button type="button" class="btn btn-secondary btn-sm" @click="applyPromo()" :disabled="promoChecking">
                                        <span x-show="!promoChecking">Appliquer</span>
                                        <span x-show="promoChecking">…</span>
                                    </button>
                                </template>
                                <template x-if="appliedPromo">
                                    <button type="button" class="btn btn-ghost btn-sm" @click="removePromo()">Retirer</button>
                                </template>
                            </div>
                            <p class="form-error mt-sm" x-show="promoError" x-text="promoError" x-cloak></p>
                            <input type="hidden" name="promo_code" :value="appliedPromo ? appliedPromo.code : ''">
                        </div>

                        <template x-if="appliedPromo">
                            <div class="info-row">
                                <span class="info-row__label">Sous-total</span>
                                <span class="font-mono" x-text="subtotalFormatted"></span>
                            </div>
                        </template>
                        <template x-if="appliedPromo">
                            <div class="info-row">
                                <span class="info-row__label" x-text="'Réduction (' + appliedPromo.code + ')'"></span>
                                <span class="font-mono" style="color:var(--color-accent);" x-text="'−' + priceFormatted(discount)"></span>
                            </div>
                        </template>

                        <div class="pos-cart__total">
                            <span class="text-muted">Total</span>
                            <span class="pos-cart__total-value" x-text="totalFormatted"></span>
                        </div>

                        <button type="submit" class="btn btn-primary pos-submit" :class="{ 'is-loading': submitting }" :disabled="cart.length === 0 || submitting">
                            <x-icon name="check" /> Valider la vente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </body>
</html>
