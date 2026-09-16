<?php

namespace App\Http\Controllers\Shop\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Point d'entrée UNIQUE pour se connecter, que ce soit un client de la boutique
 * ou un membre de l'équipe (admin/vendeur) — il n'y a plus de formulaire de
 * connexion séparé pour le back-office. On détermine le type de compte par
 * l'email avant de tenter l'authentification, puis on redirige vers l'espace
 * qui correspond (/admin pour l'équipe, /mon-compte pour un client).
 */
class AuthenticatedCustomerSessionController extends Controller
{
    public function create(Request $request): View
    {
        return view('shop.auth.login', [
            'redirectTo' => $request->query('redirect_to'),
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = (string) $request->string('email');
        $throttleKey = Str::transliterate(Str::lower($email).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)]),
            ]);
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // L'email appartient soit à un compte équipe, soit à un client — jamais les
        // deux à la fois en pratique (tables indépendantes), mais si ça arrivait,
        // l'équipe est prioritaire pour rester déterministe.
        if (User::where('email', $email)->exists()) {
            return $this->attemptStaffLogin($request, $credentials, $remember, $throttleKey);
        }

        if (! Auth::guard('customer')->attempt($credentials, $remember)) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        $target = $this->safeRedirectTarget($request, (string) $request->string('redirect_to'), adminArea: false)
            ?? $this->safeRedirectTarget($request, (string) $request->session()->pull('url.intended'), adminArea: false);

        return redirect()->to($target ?? route('customer.account'));
    }

    /**
     * @throws ValidationException
     */
    private function attemptStaffLogin(Request $request, array $credentials, bool $remember, string $throttleKey): RedirectResponse
    {
        if (! Auth::guard('web')->attempt($credentials, $remember)) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        if (! Auth::guard('web')->user()->is_active) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => 'Ce compte a été désactivé. Contactez un administrateur.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        ActivityLog::record('auth.login', Auth::guard('web')->user()->name . ' s\'est connecté(e).');

        $target = $this->safeRedirectTarget($request, (string) $request->session()->pull('url.intended'), adminArea: true);

        return redirect()->to($target ?? route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * `url.intended` est une clé de session partagée entre les deux guards — une visite
     * non authentifiée côté admin peut y laisser une URL /admin, et inversement côté
     * boutique. On ne l'honore que si elle correspond bien à la zone attendue.
     */
    private function safeRedirectTarget(Request $request, string $url, bool $adminArea): ?string
    {
        if ($url === '') {
            return null;
        }

        if (parse_url($url, PHP_URL_HOST) !== $request->getHost()) {
            return null;
        }

        $isAdminPath = str_starts_with(parse_url($url, PHP_URL_PATH) ?? '', '/admin');

        return $isAdminPath === $adminArea ? $url : null;
    }
}
