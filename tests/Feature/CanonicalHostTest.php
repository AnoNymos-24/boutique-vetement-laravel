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

        $response->assertRedirect('https://exemple.com/boutique?search=caftan');
        $response->assertStatus(301);
    }

    public function test_matching_host_is_not_redirected(): void
    {
        config(['app.url' => 'https://exemple.com']);

        $response = $this->get('https://exemple.com/');

        $response->assertOk();
    }

    public function test_localhost_app_url_never_redirects(): void
    {
        config(['app.url' => 'http://localhost']);

        $response = $this->get('http://127.0.0.1/');

        $response->assertOk();
    }
}
