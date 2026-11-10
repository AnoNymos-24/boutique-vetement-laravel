<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Verrouille le contournement du lien symbolique public/storage (bloqué en
 * 403 par certains hébergeurs mutualisés) : les images doivent rester
 * accessibles en étant servies directement par Laravel.
 */
class MediaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_serves_an_existing_file_from_the_public_disk(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/test.png', UploadedFile::fake()->image('test.png')->get());

        $response = $this->get(route('media.show', ['path' => 'products/test.png']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
    }

    public function test_missing_file_returns_404(): void
    {
        Storage::fake('public');

        $this->get(route('media.show', ['path' => 'products/inconnu.png']))->assertNotFound();
    }

    public function test_path_traversal_is_rejected(): void
    {
        Storage::fake('public');

        $this->get('/media/..%2F..%2F.env')->assertNotFound();
    }
}
