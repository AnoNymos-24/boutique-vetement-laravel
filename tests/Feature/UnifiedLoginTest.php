<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verrouille le repli vers le compte client quand une même adresse e-mail
 * appartient à la fois à un compte équipe et à un compte client (avec des
 * mots de passe différents) — sans ce repli, le compte client devient
 * injoignable depuis /connexion quel que soit le mot de passe tapé, puisque
 * l'équipe est toujours tentée en premier pour cette adresse.
 */
class UnifiedLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_login_succeeds_with_staff_password(): void
    {
        User::factory()->create(['email' => 'partagee@example.test', 'password' => 'mot-de-passe-equipe', 'role' => 'admin']);

        $response = $this->post(route('customer.login'), [
            'email' => 'partagee@example.test',
            'password' => 'mot-de-passe-equipe',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated('web');
    }

    public function test_customer_login_falls_back_when_email_is_shared_with_a_staff_account(): void
    {
        User::factory()->create(['email' => 'partagee@example.test', 'password' => 'mot-de-passe-equipe', 'role' => 'admin']);
        Customer::factory()->create(['email' => 'partagee@example.test', 'password' => 'mot-de-passe-client']);

        $response = $this->post(route('customer.login'), [
            'email' => 'partagee@example.test',
            'password' => 'mot-de-passe-client',
        ]);

        $response->assertRedirect(route('customer.account'));
        $this->assertAuthenticated('customer');
        $this->assertGuest('web');
    }

    public function test_wrong_password_for_both_accounts_still_fails(): void
    {
        User::factory()->create(['email' => 'partagee@example.test', 'password' => 'mot-de-passe-equipe', 'role' => 'admin']);
        Customer::factory()->create(['email' => 'partagee@example.test', 'password' => 'mot-de-passe-client']);

        $response = $this->post(route('customer.login'), [
            'email' => 'partagee@example.test',
            'password' => 'ni-lun-ni-lautre',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('web');
        $this->assertGuest('customer');
    }
}
