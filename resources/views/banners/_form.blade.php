@php $banner ??= null; @endphp

<div class="form-group">
    <x-input-label for="title">Titre <span class="required-mark">*</span></x-input-label>
    <x-text-input id="title" name="title" :value="old('title', $banner?->title)" required placeholder="Ex. Nouvelle collection été" />
    <x-input-error :messages="$errors->get('title')" />
</div>

<div class="form-group">
    <x-input-label for="subtitle" value="Sous-titre" />
    <x-text-input id="subtitle" name="subtitle" :value="old('subtitle', $banner?->subtitle)" placeholder="Ex. Jusqu'à -30% sur une sélection" />
    <x-input-error :messages="$errors->get('subtitle')" />
</div>

<div class="flex gap-lg">
    <div class="form-group" style="flex:1;">
        <x-input-label for="promo_text" value="Texte promo / offre" />
        <x-text-input id="promo_text" name="promo_text" :value="old('promo_text', $banner?->promo_text)" placeholder="Ex. -30%" />
    </div>

    <div class="form-group" style="flex:1;">
        <x-input-label for="sort_order" value="Ordre d'affichage" />
        <x-text-input id="sort_order" type="number" min="0" name="sort_order" :value="old('sort_order', $banner?->sort_order ?? 0)" />
        <p class="form-hint">Les bannières s'affichent par ordre croissant dans le carrousel.</p>
    </div>
</div>

<div class="flex gap-lg">
    <div class="form-group" style="flex:1;">
        <x-input-label for="button_text" value="Texte du bouton" />
        <x-text-input id="button_text" name="button_text" :value="old('button_text', $banner?->button_text)" placeholder="Ex. J'en profite" />
    </div>

    <div class="form-group" style="flex:1;">
        <x-input-label for="button_link" value="Lien du bouton" />
        <x-text-input id="button_link" name="button_link" :value="old('button_link', $banner?->button_link)" placeholder="/boutique" />
        <x-input-error :messages="$errors->get('button_link')" />
    </div>
</div>

<div class="flex gap-lg">
    <div class="form-group" style="flex:1;">
        <x-input-label for="starts_at" value="Date de début" />
        <x-text-input id="starts_at" type="date" name="starts_at" :value="old('starts_at', $banner?->starts_at?->format('Y-m-d'))" />
        <p class="form-hint">Optionnel — laisser vide pour une diffusion immédiate.</p>
    </div>

    <div class="form-group" style="flex:1;">
        <x-input-label for="expires_at" value="Date de fin" />
        <x-text-input id="expires_at" type="date" name="expires_at" :value="old('expires_at', $banner?->expires_at?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('expires_at')" />
    </div>
</div>

<div class="form-group" x-data="{ preview: null }">
    <x-input-label for="image">Image de fond {{ $banner ? '' : '(requis)' }}</x-input-label>

    <div class="flex items-center gap-lg">
        <template x-if="!preview">
            @if ($banner?->image_path)
                <img src="{{ $banner->imageUrl() }}" alt="Image actuelle" style="width:120px; height:64px; object-fit:cover; border-radius:var(--radius-md); border:1px solid var(--color-border);">
            @else
                <span class="product-thumb product-thumb--empty" style="width:120px; height:64px;"><x-icon name="image" /></span>
            @endif
        </template>
        <template x-if="preview">
            <img :src="preview" alt="Aperçu" style="width:120px; height:64px; object-fit:cover; border-radius:var(--radius-md); border:1px solid var(--color-border);">
        </template>

        <div style="flex:1;">
            <input id="image" type="file" name="image" accept="image/png,image/jpeg,image/webp"
                   class="form-input" style="padding:.5rem;" {{ $banner ? '' : 'required' }}
                   @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
            <p class="form-hint">JPG, PNG ou WebP, 4 Mo max. Format large recommandé (ex. 1600×700).</p>
        </div>
    </div>
    <x-input-error :messages="$errors->get('image')" />
</div>

<div class="form-group">
    <label class="checkbox-row">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $banner?->is_active ?? true))>
        <span>Bannière active</span>
    </label>
</div>
