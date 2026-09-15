<?php

namespace App\Http\Controllers\Shop\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

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

        $throttleKey = Str::transliterate(Str::lower($request->string('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)]),
            ]);
        }

        if (! Auth::guard('customer')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        $target = $this->safeRedirectTarget($request, (string) $request->string('redirect_to'))
            ?? $this->safeRedirectTarget($request, (string) $request->session()->pull('url.intended'));

        return redirect()->to($target ?? route('customer.account'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * `url.intended` is a single session key shared with the staff `web` guard — an
     * unauthenticated visit to /admin/* earlier in the same browser session can leave a
     * stale admin URL there. Never honor a redirect target outside this host or under /admin.
     */
    private function safeRedirectTarget(Request $request, string $url): ?string
    {
        if ($url === '') {
            return null;
        }

        if (parse_url($url, PHP_URL_HOST) !== $request->getHost()) {
            return null;
        }

        if (str_starts_with(parse_url($url, PHP_URL_PATH) ?? '', '/admin')) {
            return null;
        }

        return $url;
    }
}
