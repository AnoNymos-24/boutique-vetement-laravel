<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <div class="breadcrumb">
                <a href="{{ route('banners.index') }}" class="link">Bannières</a>
                <x-icon name="chevron-right" />
                <span>{{ $banner->title }}</span>
            </div>
            <h1 class="h1">Modifier la bannière</h1>
        </div>
    </div>

    <div class="card" data-reveal style="max-width:680px;">
        <form method="POST" action="{{ route('banners.update', $banner) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('banners._form')

            <div class="flex gap-md mt-xl">
                <button type="submit" class="btn btn-primary"><x-icon name="check" /> Enregistrer</button>
                <a href="{{ route('banners.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</x-app-layout>
