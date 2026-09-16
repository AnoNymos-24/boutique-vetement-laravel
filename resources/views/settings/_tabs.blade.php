@php $current = request()->route()->getName(); @endphp

<div class="settings-tabs" data-reveal>
    <a href="{{ route('settings.identity.edit') }}" class="settings-tab {{ $current === 'settings.identity.edit' ? 'is-active' : '' }}">
        <x-icon name="settings" /> Identité
    </a>
    <a href="{{ route('settings.categories.index') }}" class="settings-tab {{ $current === 'settings.categories.index' ? 'is-active' : '' }}">
        <x-icon name="grid" /> Catégories
    </a>
    <a href="{{ route('settings.taxonomies.index') }}" class="settings-tab {{ $current === 'settings.taxonomies.index' ? 'is-active' : '' }}">
        <x-icon name="star" /> Étiquettes &amp; tags
    </a>
    @if (auth()->user()?->isAdmin())
        <a href="{{ route('settings.mail.edit') }}" class="settings-tab {{ $current === 'settings.mail.edit' ? 'is-active' : '' }}">
            <x-icon name="mail" /> E-mail
        </a>
        <a href="{{ route('settings.payment.edit') }}" class="settings-tab {{ $current === 'settings.payment.edit' ? 'is-active' : '' }}">
            <x-icon name="cash" /> Paiement
        </a>
        <a href="{{ route('settings.terms.edit') }}" class="settings-tab {{ $current === 'settings.terms.edit' ? 'is-active' : '' }}">
            <x-icon name="shield" /> Conditions
        </a>
    @endif
</div>
