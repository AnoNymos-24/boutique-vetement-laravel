<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verrouille le correctif "www vs sans www" : une requête sur un hôte qui ne
 * correspond pas à APP_URL doit être redirigée vers l'hôte canonique avant
 * que la session ne démarre, pour qu'une cliente n'atterrisse jamais sur deux
 * cookies de session différents selon l'URL utilisée.
 */
class CanonicalHostTest extends TestCase
{
    use RefreshDatabase;

    public function test_mismatched_host_redirects_to_canonical_app_url_host(): void
    {
        config(['app.url' => 'https://exemple.com']);

        $response = $this->get('http://www.exemple.com/boutique?search=caftan');

        // Schéma préservé tel que vu par la requête (jamais forcé en https) :
        // comparer/forcer le schéma créait une boucle de redirection infinie
        // derrière un proxy qui termine le HTTPS en amont (ex. Hostinger).
        $response->assertRedirect('http://exemple.com/boutique?search=caftan');
        $response->assertStatus(301);
    }

    public function test_matching_host_is_not_redirected_regardless_of_scheme(): void
    {
        config(['app.url' => 'https://exemple.com']);

        // http alors que APP_URL est https : ne doit PAS rediriger (c'est
        // exactement le cas d'un proxy qui termine le HTTPS en amont).
        $response = $this->get('http://exemple.com/');

        $response->assertOk();
    }

    public function test_localhost_app_url_never_redirects(): void
    {
        config(['app.url' => 'http://localhost']);

        $response = $this->get('http://127.0.0.1/');

        $response->assertOk();
    }
}
