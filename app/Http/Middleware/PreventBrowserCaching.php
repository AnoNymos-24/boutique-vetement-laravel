<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Empêche le navigateur (surtout mobile — Safari/Chrome iOS/Android mettent
 * les pages en cache plus agressivement que le desktop, sans "Ctrl+F5" facile
 * pour forcer un rechargement) de garder une vieille version HTML du back-office
 * en cache après un déploiement : chaque page admin doit toujours refléter les
 * derniers changements de style/comportement, pas une version figée au moment
 * de la première visite. Les fichiers statiques dans public/build/ (CSS/JS,
 * hashés par contenu via Vite) ne sont eux jamais concernés par ce middleware —
 * ils ne passent pas par les routes Laravel — donc restent mis en cache normalement.
 */
class PreventBrowserCaching
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
