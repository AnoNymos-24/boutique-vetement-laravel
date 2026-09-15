<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <h1 class="h1">Équipe</h1>
            <p class="text-muted text-sm">{{ $users->total() }} compte(s) — administrateurs et vendeurs.</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <x-icon name="plus" /> Nouveau compte
        </a>
    </div>

    <form method="GET" class="table-toolbar" data-reveal>
        <div class="search-input" style="max-width:280px;">
            <x-icon name="search" />
            <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="Nom ou email…">
        </div>

        <div class="table-filters">
            <select name="role" class="form-select" onchange="this.form.submit()">
                <option value="">Tous les rôles</option>
                <option value="admin" @selected(request('role') === 'admin')>Administrateur</option>
                <option value="vendeur" @selected(request('role') === 'vendeur')>Vendeur</option>
            </select>

            <button type="submit" class="btn btn-secondary btn-sm">Filtrer</button>
            @if (request()->anyFilled(['search', 'role']))
                <a href="{{ route('users.index') }}" class="btn btn-ghost btn-sm">Réinitialiser</a>
            @endif
        </div>
    </form>

    <div class="table-wrapper" data-reveal>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Compte</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th class="cell-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td class="cell-primary">
                            <div>{{ $user->name }}</div>
                            <div class="text-xs text-muted">{{ $user->email }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $user->role === 'admin' ? 'badge--success' : 'badge--muted' }}">
                                {{ $user->role === 'admin' ? 'Administrateur' : 'Vendeur' }}
                            </span>
                        </td>
                        <td>
                            @if ($user->is_active)
                                <span class="badge badge--success"><x-icon name="check" /> Actif</span>
                            @else
                                <span class="badge badge--destructive">Désactivé</span>
                            @endif
                        </td>
                        <td class="cell-actions">
                            <div class="flex gap-sm" style="justify-content:flex-end;">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-ghost btn-icon" title="Modifier"><x-icon name="edit" /></a>

                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('users.toggle', $user) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-secondary btn-sm">
                                            {{ $user->is_active ? 'Désactiver' : 'Activer' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('users.destroy', $user) }}"
                                          onsubmit="return confirm('Supprimer le compte « {{ addslashes($user->name) }} » ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-icon" title="Supprimer"><x-icon name="trash" /></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-muted text-sm" style="text-align:center; padding: var(--space-3xl);">
                            Aucun compte ne correspond à ces filtres.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $users->appends(request()->query())->links('pagination.custom') }}
    </div>
</x-app-layout>
