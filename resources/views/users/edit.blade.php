<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <div class="breadcrumb">
                <a href="{{ route('users.index') }}" class="link">Équipe</a>
                <x-icon name="chevron-right" />
                <span>{{ $user->name }}</span>
            </div>
            <h1 class="h1">Modifier le compte</h1>
        </div>
    </div>

    <div class="card" data-reveal style="max-width:560px;">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')
            @include('users._form')

            <div class="flex gap-md mt-xl">
                <button type="submit" class="btn btn-primary"><x-icon name="check" /> Enregistrer</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</x-app-layout>
