<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Un seul formulaire de connexion pour tout le monde (équipe et clients) — voir
        // Shop\Auth\AuthenticatedCustomerSessionController::store(), qui détecte le type
        // de compte et redirige vers /admin ou /mon-compte en conséquence.
        $middleware->redirectGuestsTo(fn ($request) => route('customer.login'));

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'no-cache' => \App\Http\Middleware\PreventBrowserCaching::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
