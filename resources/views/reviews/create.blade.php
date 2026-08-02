<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <div class="breadcrumb">
                <a href="{{ route('reviews') }}" class="link">Avis</a>
                <x-icon name="chevron-right" />
                <span>Nouveau</span>
            </div>
            <h1 class="h1">Ajouter un avis</h1>
            <p class="text-muted text-sm">Pour transcrire un retour reçu par WhatsApp, téléphone ou réseaux sociaux.</p>
        </div>
    </div>

    <div class="card" data-reveal style="max-width:600px;">
        <form method="POST" action="{{ route('reviews.store') }}">
            @csrf

            <div class="form-group">
                <x-input-label for="product_id">Produit <span class="required-mark">*</span></x-input-label>
                <select id="product_id" name="product_id" class="form-select" required>
                    <option value="" disabled selected>Choisir un produit…</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected((int) old('product_id') === $product->id)>{{ $product->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('product_id')" />
            </div>

            <div class="form-group">
                <x-input-label for="author_name">Nom du client <span class="required-mark">*</span></x-input-label>
                <x-text-input id="author_name" name="author_name" :value="old('author_name')" required placeholder="Ex. Aïcha D." />
                <x-input-error :messages="$errors->get('author_name')" />
            </div>

            <div class="form-group">
                <x-input-label for="rating">Note <span class="required-mark">*</span></x-input-label>
                <select id="rating" name="rating" class="form-select" required>
                    @for ($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" @selected(old('rating', 5) == $i)>{{ $i }} étoile{{ $i > 1 ? 's' : '' }}</option>
                    @endfor
                </select>
                <x-input-error :messages="$errors->get('rating')" />
            </div>

            <div class="form-group">
                <x-input-label for="comment">Commentaire <span class="required-mark">*</span></x-input-label>
                <textarea id="comment" name="comment" class="form-textarea" rows="4" required placeholder="Ce que le client a dit…">{{ old('comment') }}</textarea>
                <x-input-error :messages="$errors->get('comment')" />
            </div>

            <div class="form-group">
                <x-input-label for="status" value="Statut" />
                <select id="status" name="status" class="form-select" required>
                    <option value="publie" @selected(old('status', 'publie') === 'publie')>Publier immédiatement</option>
                    <option value="en_attente" @selected(old('status') === 'en_attente')>Mettre en attente de modération</option>
                </select>
            </div>

            <div class="flex gap-md mt-xl">
                <button type="submit" class="btn btn-primary"><x-icon name="check" /> Enregistrer l'avis</button>
                <a href="{{ route('reviews') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</x-app-layout>
