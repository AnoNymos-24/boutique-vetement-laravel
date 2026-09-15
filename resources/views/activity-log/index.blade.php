<x-app-layout>
    <div class="page-header" data-reveal>
        <div class="page-header__title">
            <h1 class="h1">Journal d'activité</h1>
            <p class="text-muted text-sm">{{ $logs->total() }} action(s) tracée(s) — visible par l'administrateur uniquement.</p>
        </div>
    </div>

    <form method="GET" class="table-toolbar" data-reveal>
        <div class="table-filters">
            <select name="user_id" class="form-select" onchange="this.form.submit()">
                <option value="">Tous les comptes</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((int) request('user_id') === $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>

            <select name="action" class="form-select" onchange="this.form.submit()">
                <option value="">Toutes les catégories</option>
                @foreach ($actionGroups as $group)
                    <option value="{{ $group }}" @selected(request('action') === $group)>{{ ucfirst($group) }}</option>
                @endforeach
            </select>

            <input type="date" name="date_from" class="form-input" value="{{ request('date_from') }}" onchange="this.form.submit()">
            <input type="date" name="date_to" class="form-input" value="{{ request('date_to') }}" onchange="this.form.submit()">

            @if (request()->anyFilled(['user_id', 'action', 'date_from', 'date_to']))
                <a href="{{ route('activity-log.index') }}" class="btn btn-ghost btn-sm">Réinitialiser</a>
            @endif
        </div>
    </form>

    <div class="table-wrapper" data-reveal>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Compte</th>
                    <th>Action</th>
                    <th>Détail</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="cell-muted text-sm">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $log->user?->name ?? '—' }}</td>
                        <td><span class="badge badge--muted font-mono">{{ $log->action }}</span></td>
                        <td>{{ $log->description }}</td>
                        <td class="cell-muted text-sm font-mono">{{ $log->ip_address ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-muted text-sm" style="text-align:center; padding: var(--space-3xl);">
                            Aucune action tracée pour ces filtres.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $logs->appends(request()->query())->links('pagination.custom') }}
    </div>
</x-app-layout>
