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
</div>
