<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        ActivityLog::record('auth.login', Auth::user()->name . ' s\'est connecté(e).');

        // `url.intended` is a single session key shared with the customer guard — an
        // unauthenticated visit to the storefront earlier in the same browser session
        // can leave a non-admin URL there. Only honor it if it's actually an admin URL.
        $intended = $request->session()->pull('url.intended');
        if ($intended && parse_url($intended, PHP_URL_HOST) === $request->getHost()
            && str_starts_with(parse_url($intended, PHP_URL_PATH) ?? '', '/admin')) {
            return redirect()->to($intended);
        }

        return redirect()->route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        ActivityLog::record('auth.logout', Auth::user()->name . ' s\'est déconnecté(e).');

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
