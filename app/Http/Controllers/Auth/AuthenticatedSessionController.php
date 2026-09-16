<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * La connexion elle-même se fait désormais via le formulaire unique
 * (Shop\Auth\AuthenticatedCustomerSessionController) — cette classe ne gère
 * plus que la déconnexion de l'équipe (guard "web").
 */
class AuthenticatedSessionController extends Controller
{
    public function destroy(Request $request): RedirectResponse
    {
        ActivityLog::record('auth.logout', Auth::user()->name . ' s\'est déconnecté(e).');

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
