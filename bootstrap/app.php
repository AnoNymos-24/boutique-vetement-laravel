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
        // Avant toute autre chose (donc avant que la session ne démarre et
        // que son cookie ne soit scellé sur l'hôte de la requête) : impose
        // l'hôte unique défini par APP_URL. Voir EnsureCanonicalHost pour le
        // bug précis que ça évite (www vs sans www = deux sessions distinctes).
        $middleware->prepend(\App\Http\Middleware\EnsureCanonicalHost::class);

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
