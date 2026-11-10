<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Sert les fichiers du disque "public" (logo, images produits, bannières)
 * directement par Laravel au lieu de passer par le lien symbolique
 * public/storage. Certains hébergements mutualisés bloquent le suivi des
 * liens symboliques par Apache (renvoie 403 même si le fichier existe
 * réellement sur le disque) ou appliquent des permissions de fichiers
 * incompatibles — cette route fonctionne quel que soit l'hébergeur, car
 * PHP lit le fichier directement sans jamais passer par le lien.
 */
class MediaController extends Controller
{
    public function show(string $path): Response|StreamedResponse
    {
        $disk = Storage::disk('public');

        // Flysystem normalise déjà les "..", mais on refuse explicitement
        // toute remontée de chemin par prudence (défense en profondeur).
        if (str_contains($path, '..') || ! $disk->exists($path)) {
            abort(404);
        }

        return $disk->response($path, null, [
            'Cache-Control' => 'public, max-age=604800, immutable',
        ]);
    }
}
