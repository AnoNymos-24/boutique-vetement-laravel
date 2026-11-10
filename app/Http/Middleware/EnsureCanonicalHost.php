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
 *
 * Volontairement limité à l'hôte, jamais au schéma (http/https) : derrière
 * un proxy qui termine le HTTPS en amont (cas de nombreux hébergeurs
 * mutualisés, dont Hostinger) sans que Laravel soit configuré pour lui faire
 * confiance, $request->getScheme() peut rapporter "http" même pour une vraie
 * requête https — comparer le schéma créait alors une boucle de redirection
 * infinie (Laravel redirige vers https, l'hébergeur renvoie vers Laravel en
 * http, etc.).
 */
class EnsureCanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $canonicalHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        if (! $canonicalHost || $canonicalHost === 'localhost' || $canonicalHost === $request->getHost()) {
            return $next($request);
        }

        $url = $request->getScheme().'://'.$canonicalHost.$request->getRequestUri();

        return redirect()->to($url, 301);
    }
}
