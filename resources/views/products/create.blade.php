<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <div class="breadcrumb">
                <a href="{{ route('products.index') }}" class="link">Produits</a>
                <x-icon name="chevron-right" />
                <span>Nouveau</span>
            </div>
            <h1 class="h1">Nouveau produit</h1>
        </div>
    </div>

    <div class="card" data-reveal style="max-width:680px;">
        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <x-input-label for="name">Nom du produit <span class="required-mark">*</span></x-input-label>
                <x-text-input id="name" name="name" :value="old('name')" required autofocus placeholder="Ex. Robe fluide imprimée" />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div class="form-group">
                <x-input-label for="description">Description <span class="required-mark">*</span></x-input-label>
                <textarea id="description" name="description" class="form-textarea" rows="4" required
                          placeholder="Matière, coupe, entretien… ce que la cliente doit savoir avant d'acheter.">{{ old('description') }}</textarea>
                <x-input-error :messages="$errors->get('description')" />
            </div>

            <div class="form-group">
                <x-input-label for="category_id">Catégorie <span class="required-mark">*</span></x-input-label>
                <select id="category_id" name="category_id" class="form-select" required>
                    <option value="" disabled @selected(!old('category_id'))>Choisir une catégorie…</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) old('category_id') === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('category_id')" />
                @if ($categories->isEmpty())
                    <p class="form-hint">Aucune catégorie n'existe encore — <a href="{{ route('settings.categories.index') }}" class="link">créez-en une</a> avant d'ajouter un produit.</p>
                @endif
            </div>

            <div class="flex gap-lg">
                <div class="form-group" style="flex:1;">
                    <x-input-label for="price">Prix de vente (€) <span class="required-mark">*</span></x-input-label>
                    <x-text-input id="price" type="number" step="0.01" min="0" name="price" :value="old('price')" required />
                    <x-input-error :messages="$errors->get('price')" />
                </div>

                <div class="form-group" style="flex:1;">
                    <x-input-label for="compare_at_price" value="Prix barré / promo (€)" />
                    <x-text-input id="compare_at_price" type="number" step="0.01" min="0" name="compare_at_price" :value="old('compare_at_price')" placeholder="Optionnel" />
                    <p class="form-hint">Laisser vide si pas de promotion.</p>
                </div>
            </div>

            <div class="form-group" style="max-width:calc(50% - var(--space-lg) / 2);">
                <x-input-label for="cost_price" value="Coût d'achat (€)" />
                <x-text-input id="cost_price" type="number" step="0.01" min="0" name="cost_price" :value="old('cost_price')" placeholder="Optionnel" />
                <p class="form-hint">Sert au calcul de la marge dans Finance. Laisser vide si inconnu pour l'instant.</p>
            </div>

            <div class="form-group" x-data="variantEditor()">
                <x-input-label value="Variantes (taille / couleur) — au moins une" />
                <p class="form-hint mt-sm" style="margin-bottom: var(--space-lg);">
                    Chaque variante a son propre stock et sa propre référence. Pour un accessoire sans déclinaison, laissez taille et couleur vides — une seule ligne suffit.
                </p>

                <template x-for="(variant, index) in variants" :key="index">
                    <div class="variant-row">
                        <div class="form-group">
                            <x-input-label value="Taille" />
                            <input type="text" class="form-input" placeholder="Ex. M" :name="'variants[' + index + '][size]'" x-model="variant.size">
                        </div>
                        <div class="form-group">
                            <x-input-label value="Couleur" />
                            <input type="text" class="form-input" placeholder="Ex. Noir" :name="'variants[' + index + '][color]'" x-model="variant.color">
                        </div>
                        <div class="form-group">
                            <x-input-label value="SKU" />
                            <input type="text" class="form-input font-mono" placeholder="BTQ-0142-M-NOIR" required :name="'variants[' + index + '][sku]'" x-model="variant.sku">
                        </div>
                        <div class="form-group">
                            <x-input-label value="Stock" />
                            <input type="number" min="0" class="form-input" required :name="'variants[' + index + '][stock_quantity]'" x-model="variant.stock_quantity">
                        </div>
                        <div class="form-group">
                            <x-input-label value="Seuil alerte" />
                            <input type="number" min="0" class="form-input" required :name="'variants[' + index + '][low_stock_threshold]'" x-model="variant.low_stock_threshold">
                        </div>
                        <button type="button" class="btn btn-ghost btn-icon variant-row__remove" @click="remove(index)" :disabled="variants.length === 1" title="Retirer cette variante">
                            <x-icon name="trash" />
                        </button>
                    </div>
                </template>

                <button type="button" class="btn btn-secondary btn-sm mt-md" @click="add()">
                    <x-icon name="plus" /> Ajouter une variante
                </button>

                <x-input-error :messages="$errors->get('variants')" class="mt-sm" />
            </div>

            <div class="form-group">
                <x-input-label for="status">Statut <span class="required-mark">*</span></x-input-label>
                <select id="status" name="status" class="form-select" required>
                    <option value="draft" @selected(old('status', 'draft') === 'draft')>Brouillon (non visible en boutique)</option>
                    <option value="active" @selected(old('status') === 'active')>Actif (visible en boutique)</option>
                    <option value="archived" @selected(old('status') === 'archived')>Archivé</option>
                </select>
            </div>

            @if ($labels->isNotEmpty())
                <div class="form-group">
                    <x-input-label value="Étiquettes" />
                    <div class="pill-checkbox-group">
                        @foreach ($labels as $label)
                            <label class="pill-checkbox">
                                <input type="checkbox" name="labels[]" value="{{ $label->id }}" @checked(in_array($label->id, old('labels', [])))>
                                {{ $label->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($tags->isNotEmpty())
                <div class="form-group">
                    <x-input-label value="Tags (référencement)" />
                    <div class="pill-checkbox-group">
                        @foreach ($tags as $tag)
                            <label class="pill-checkbox">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(in_array($tag->id, old('tags', [])))>
                                {{ $tag->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($labels->isEmpty() || $tags->isEmpty())
                <p class="form-hint mt-sm" style="margin-bottom: var(--space-xl);">
                    Gérez les étiquettes et tags disponibles depuis
                    <a href="{{ route('settings.taxonomies.index') }}" class="link">Paramètres → Étiquettes &amp; tags</a>.
                </p>
            @endif

            <div class="form-group" x-data="imageUploader()">
                <x-input-label for="images">Photos du produit <span class="required-mark">*</span></x-input-label>
                <input id="images" type="file" name="images[]" multiple accept="image/png,image/jpeg,image/webp"
                       class="form-input" required @change="onChange($event)" style="padding:.5rem;">
                <p class="form-hint">Au moins 2 photos (JPG, PNG ou WebP, 4 Mo max chacune).</p>
                <x-input-error :messages="$errors->get('images')" />
                <x-input-error :messages="$errors->get('images.0')" />

                <div class="image-preview-grid mt-md" x-show="previews.length > 0" x-cloak>
                    <template x-for="(src, i) in previews" :key="i">
                        <img :src="src" class="image-preview-thumb">
                    </template>
                </div>
                <p class="form-error mt-sm" x-show="previews.length === 1" x-cloak>Il manque au moins une photo (minimum 2).</p>
            </div>

            <div class="flex gap-md mt-xl">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="check" /> Enregistrer le produit
                </button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</x-app-layout>
