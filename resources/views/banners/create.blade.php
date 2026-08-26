<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <div class="breadcrumb">
                <a href="{{ route('banners.index') }}" class="link">Bannières</a>
                <x-icon name="chevron-right" />
                <span>Nouvelle</span>
            </div>
            <h1 class="h1">Nouvelle bannière</h1>
        </div>
    </div>

    <div class="card" data-reveal style="max-width:680px;">
        <form method="POST" action="{{ route('banners.store') }}" enctype="multipart/form-data">
            @csrf
            @include('banners._form')

            <div class="flex gap-md mt-xl">
                <button type="submit" class="btn btn-primary"><x-icon name="check" /> Enregistrer la bannière</button>
                <a href="{{ route('banners.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</x-app-layout>
