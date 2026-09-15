<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->search, fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
            ))
            ->when($request->role, fn ($q, $role) => $q->where('role', $role))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', ['users' => $users]);
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,vendeur'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => $data['password'],
        ]);

        ActivityLog::record('user.created', "Compte « {$user->name} » ({$user->role}) créé.", $user);

        return redirect()->route('users.index')->with('status', "Compte « {$user->name} » créé.");
    }

    public function edit(User $user)
    {
        return view('users.edit', ['user' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:admin,vendeur'],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        if ($user->id === Auth::id() && $data['role'] !== 'admin') {
            return back()->withInput()->with('error', 'Vous ne pouvez pas retirer vos propres droits administrateur.');
        }

        $roleChanged = $data['role'] !== $user->role;

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
        ]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        if ($roleChanged) {
            ActivityLog::record('user.role_changed', "Rôle de « {$user->name} » changé en {$user->role}.", $user);
        } else {
            ActivityLog::record('user.updated', "Compte « {$user->name} » modifié.", $user);
        }

        return redirect()->route('users.index')->with('status', "Compte « {$user->name} » mis à jour.");
    }

    public function toggleActive(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        ActivityLog::record(
            $user->is_active ? 'user.activated' : 'user.deactivated',
            ($user->is_active ? 'Compte activé : ' : 'Compte désactivé : ') . $user->name,
            $user
        );

        return back()->with('status', $user->is_active ? 'Compte activé.' : 'Compte désactivé.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $name = $user->name;
        $user->delete();

        ActivityLog::record('user.deleted', "Compte « {$name} » supprimé.");

        return back()->with('status', "Compte « {$name} » supprimé.");
    }
}
