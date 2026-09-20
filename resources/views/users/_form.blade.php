@php $user ??= null; @endphp

<div class="form-group">
    <x-input-label for="name">Nom complet <span class="required-mark">*</span></x-input-label>
    <x-text-input id="name" name="name" :value="old('name', $user?->name)" required autofocus />
    <x-input-error :messages="$errors->get('name')" />
</div>

<div class="form-group">
    <x-input-label for="email">Email <span class="required-mark">*</span></x-input-label>
    <x-text-input id="email" type="email" name="email" :value="old('email', $user?->email)" required autocomplete="off" />
    <x-input-error :messages="$errors->get('email')" />
</div>

<div class="form-group">
    <x-input-label for="role">Rôle <span class="required-mark">*</span></x-input-label>
    <select id="role" name="role" class="form-select" required
            @if ($user && $user->id === auth()->id()) disabled @endif>
        <option value="admin" @selected(old('role', $user?->role) === 'admin')>Administrateur — accès complet, gestion des comptes, traçabilité</option>
        <option value="vendeur" @selected(old('role', $user?->role ?? 'vendeur') === 'vendeur')>Vendeur — accès aux ventes/commandes/clients, pas à l'équipe ni au journal</option>
    </select>
    @if ($user && $user->id === auth()->id())
        <input type="hidden" name="role" value="{{ $user->role }}">
        <p class="form-hint">Vous ne pouvez pas changer votre propre rôle.</p>
    @endif
    <x-input-error :messages="$errors->get('role')" />
</div>

<div class="form-group">
    <x-input-label for="password">
        {{ $user ? 'Nouveau mot de passe' : 'Mot de passe' }}
        @unless ($user) <span class="required-mark">*</span> @endunless
    </x-input-label>
    <x-text-input id="password" type="password" name="password" :required="! $user" minlength="8" autocomplete="new-password" />
    <p class="form-hint">{{ $user ? 'Laisser vide pour conserver le mot de passe actuel.' : 'Minimum 8 caractères.' }}</p>
    <x-input-error :messages="$errors->get('password')" />
</div>

<div class="form-group">
    <x-input-label for="password_confirmation" value="Confirmation du mot de passe" />
    <x-text-input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" />
</div>
