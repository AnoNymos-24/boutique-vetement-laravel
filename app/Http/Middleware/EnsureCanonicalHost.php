<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Le cookie de session est verrouillé sur l'hôte exact de la requête
 * (SESSION_DOMAIN vide par défaut). Sans cette redirection, un même site
 * accessible à la fois en "exemple.com" et "www.exemple.com" (cas fréquent
 * chez la plupart des hébergeurs) crée deux sessions totalement séparées :
 * une cliente qui termine une réinitialisation de mot de passe sur l'une des
 * deux variantes, puis navigue ou se connecte sur l'autre, perd sa session
 * sans message d'erreur clair — symptôme exact de "la réinitialisation ou la
 * connexion ne fonctionne pas, mais seulement pour certaines clientes".
 * On redirige donc systématiquement vers l'hôte unique défini par APP_URL.
 */
class EnsureCanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $appUrl = (string) config('app.url');
        $canonicalHost = parse_url($appUrl, PHP_URL_HOST);
        $canonicalScheme = parse_url($appUrl, PHP_URL_SCHEME) ?: $request->getScheme();

        if (! $canonicalHost || $canonicalHost === 'localhost') {
            return $next($request);
        }

        if ($canonicalHost === $request->getHost() && $canonicalScheme === $request->getScheme()) {
            return $next($request);
        }

        $url = $canonicalScheme.'://'.$canonicalHost.$request->getRequestUri();

        return redirect()->to($url, 301);
    }
}
