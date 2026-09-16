<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <h1 class="h1">Paramètres</h1>
            <p class="text-muted text-sm">Identité, catégories et étiquettes de la boutique.</p>
        </div>
    </div>

    @include('settings._tabs')

    <div class="card" data-reveal style="max-width:560px;">
        <div class="card-header">
            <h2 class="card-title">Identité de la boutique</h2>
        </div>

        <form method="POST" action="{{ route('settings.identity.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <x-input-label for="name" value="Nom de la boutique" />
                <x-text-input id="name" name="name" :value="old('name', $setting->name)" required />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div class="form-group">
                <x-input-label for="description" value="Description" />
                <textarea id="description" name="description" class="form-textarea" rows="4" placeholder="Une phrase qui présente la boutique aux clients.">{{ old('description', $setting->description) }}</textarea>
                <x-input-error :messages="$errors->get('description')" />
            </div>

            <div class="form-group">
                <x-input-label for="currency_code" value="Devise" />
                <select id="currency_code" name="currency_code" class="form-select" required>
                    @foreach (\App\Models\StoreSetting::currencies() as $code => $meta)
                        <option value="{{ $code }}" @selected(old('currency_code', $setting->currency_code) === $code)>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
                <p class="form-hint">S'applique immédiatement à tous les prix affichés, sur la boutique comme dans le back-office.</p>
                <x-input-error :messages="$errors->get('currency_code')" />
            </div>

            <div class="form-group" x-data="{ preview: null }">
                <x-input-label for="logo" value="Logo" />

                <div class="flex items-center gap-lg">
                    <template x-if="!preview">
                        @if ($setting->logoUrl())
                            <img src="{{ $setting->logoUrl() }}" alt="Logo actuel" style="width:64px; height:64px; object-fit:contain; border-radius:var(--radius-md); border:1px solid var(--color-border); background:#fff;">
                        @else
                            <span class="product-thumb product-thumb--empty" style="width:64px; height:64px;"><x-icon name="image" /></span>
                        @endif
                    </template>
                    <template x-if="preview">
                        <img :src="preview" alt="Aperçu" style="width:64px; height:64px; object-fit:contain; border-radius:var(--radius-md); border:1px solid var(--color-border); background:#fff;">
                    </template>

                    <div style="flex:1;">
                        <input id="logo" type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml"
                               class="form-input" style="padding:.5rem;"
                               @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                        <p class="form-hint">PNG, JPG, WebP ou SVG, 2 Mo max. Affiché dans l'en-tête et le pied de page de la boutique.</p>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('logo')" />

                @if ($setting->logoUrl())
                    <label class="checkbox-row mt-md">
                        <input type="checkbox" name="remove_logo" value="1">
                        <span>Retirer le logo actuel</span>
                    </label>
                @endif
            </div>

            <div class="form-group">
                <x-input-label value="Réseaux sociaux" />
                <p class="form-hint" style="margin-bottom: var(--space-md);">Affichés dans le pied de page de la boutique.</p>

                <div class="flex gap-md" style="margin-bottom: var(--space-md); align-items:flex-start;">
                    <x-icon name="facebook" class="icon" style="margin-top:.7rem; flex-shrink:0;" />
                    <div style="flex:1;">
                        <x-text-input name="facebook_url" placeholder="https://facebook.com/…" :value="old('facebook_url', $setting->facebook_url)" />
                        <x-input-error :messages="$errors->get('facebook_url')" />
                    </div>
                </div>

                <div class="flex gap-md" style="margin-bottom: var(--space-md); align-items:flex-start;">
                    <x-icon name="instagram" class="icon" style="margin-top:.7rem; flex-shrink:0;" />
                    <div style="flex:1;">
                        <x-text-input name="instagram_url" placeholder="https://instagram.com/…" :value="old('instagram_url', $setting->instagram_url)" />
                        <x-input-error :messages="$errors->get('instagram_url')" />
                    </div>
                </div>

                <div class="flex gap-md" style="margin-bottom: var(--space-md); align-items:flex-start;">
                    <x-icon name="tiktok" class="icon" style="margin-top:.7rem; flex-shrink:0;" />
                    <div style="flex:1;">
                        <x-text-input name="tiktok_url" placeholder="https://tiktok.com/@…" :value="old('tiktok_url', $setting->tiktok_url)" />
                        <x-input-error :messages="$errors->get('tiktok_url')" />
                    </div>
                </div>

                <div class="flex gap-md" style="margin-bottom: var(--space-md); align-items:flex-start;">
                    <x-icon name="snapchat" class="icon" style="margin-top:.7rem; flex-shrink:0;" />
                    <div style="flex:1;">
                        <x-text-input name="snapchat_url" placeholder="https://snapchat.com/add/…" :value="old('snapchat_url', $setting->snapchat_url)" />
                        <x-input-error :messages="$errors->get('snapchat_url')" />
                    </div>
                </div>

                <div class="flex gap-md" style="margin-bottom: var(--space-md); align-items:flex-start;">
                    <x-icon name="youtube" class="icon" style="margin-top:.7rem; flex-shrink:0;" />
                    <div style="flex:1;">
                        <x-text-input name="youtube_url" placeholder="https://youtube.com/@…" :value="old('youtube_url', $setting->youtube_url)" />
                        <x-input-error :messages="$errors->get('youtube_url')" />
                    </div>
                </div>

                <div class="flex gap-md" style="align-items:flex-start;">
                    <x-icon name="linkedin" class="icon" style="margin-top:.7rem; flex-shrink:0;" />
                    <div style="flex:1;">
                        <x-text-input name="linkedin_url" placeholder="https://linkedin.com/company/…" :value="old('linkedin_url', $setting->linkedin_url)" />
                        <x-input-error :messages="$errors->get('linkedin_url')" />
                    </div>
                </div>
            </div>

            <div class="form-group">
                <x-input-label value="Localisation" />
                <p class="form-hint" style="margin-bottom: var(--space-md);">Affichées dans le pied de page de la boutique.</p>

                <div class="flex gap-md" style="margin-bottom: var(--space-md); align-items:flex-start;">
                    <x-icon name="map-pin" class="icon" style="margin-top:.7rem; flex-shrink:0;" />
                    <div style="flex:1;">
                        <x-input-label for="google_maps_url" value="Lien Google Maps" />
                        <x-text-input id="google_maps_url" name="google_maps_url" placeholder="https://maps.app.goo.gl/…" :value="old('google_maps_url', $setting->google_maps_url)" />
                        <x-input-error :messages="$errors->get('google_maps_url')" />
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <x-input-label for="address" value="Adresse physique" />
                    <x-text-input id="address" name="address" placeholder="12 avenue Habib Bourguiba, Tunis" :value="old('address', $setting->address)" />
                    <p class="form-hint">Affichée en texte dans le pied de page, à côté du lien Google Maps.</p>
                    <x-input-error :messages="$errors->get('address')" />
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <x-icon name="check" /> Enregistrer
            </button>
        </form>
    </div>
</x-app-layout>
