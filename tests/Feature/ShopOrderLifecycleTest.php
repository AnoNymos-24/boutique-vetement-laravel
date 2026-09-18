<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Refund;
use App\Models\Review;
use App\Models\User;
use App\Notifications\ComplaintReplyNotification;
use App\Notifications\OrderConfirmationNotification;
use App\Notifications\OrderPaymentStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Couvre le parcours complet boutique -> back-office qui, à l'inverse des
 * tests d'auth Breeze, n'était vérifié que manuellement (curl/tinker) au fil
 * des sessions précédentes : commande en ligne, vérification de paiement
 * (et décrément de stock associé), avis client, réclamation et remboursement.
 */
class ShopOrderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_order_lifecycle_from_checkout_to_refund(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        // Email explicite : un vrai client boutique en ligne en a toujours un
        // (inscription obligatoire), contrairement à un client comptoir POS —
        // CustomerFactory n'en génère un que dans 40% des cas par défaut.
        $customer = Customer::factory()->create(['email' => 'client.test@example.test']);
        $product = Product::factory()->create(['status' => 'active', 'price' => 5000]);
        $variant = $product->variants()->first();
        $variant->update(['stock_quantity' => 10]);

        // --- Panier + checkout (paiement à la livraison) ---
        $this->actingAs($customer, 'customer')
            ->post(route('shop.cart.add'), ['product_variant_id' => $variant->id, 'quantity' => 2])
            ->assertSessionHasNoErrors();

        $response = $this->actingAs($customer, 'customer')->post(route('shop.checkout.store'), [
            'customer_name' => $customer->name,
            'customer_phone' => '0102030405',
            'customer_address' => 'Cocody, Abidjan',
            'payment_method' => 'especes',
        ]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $order = Order::where('customer_id', $customer->id)->firstOrFail();
        $this->assertSame(10000, $order->total);
        $this->assertSame('en_attente', $order->status);
        $this->assertSame('en_attente_verification', $order->payment_status);
        Notification::assertSentTo($customer, OrderConfirmationNotification::class);

        // Le stock ne doit être décrémenté qu'à la vérification du paiement, pas à la commande.
        $this->assertSame(10, $variant->fresh()->stock_quantity);

        // --- Vérification du paiement (admin) ---
        $this->actingAs($admin)
            ->patch(route('orders.payment', $order), ['action' => 'verifie'])
            ->assertRedirect();

        $this->assertSame(8, $variant->fresh()->stock_quantity);
        Notification::assertSentTo($customer, OrderPaymentStatusNotification::class);

        // --- Avis client (publication automatique) ---
        $reviewResponse = $this->actingAs($customer, 'customer')->post(route('customer.reviews.store'), [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Très bon produit, livraison rapide.',
        ]);
        $reviewResponse->assertSessionHasNoErrors();

        $review = Review::where('customer_id', $customer->id)->where('product_id', $product->id)->firstOrFail();
        $this->assertSame('publie', $review->status);

        // --- Réponse admin à l'avis ---
        $this->actingAs($admin)
            ->post(route('reviews.reply', $review), ['admin_reply' => 'Merci pour votre retour !'])
            ->assertRedirect();
        $this->assertSame('Merci pour votre retour !', $review->fresh()->admin_reply);

        // --- Réclamation client ---
        $complaintResponse = $this->actingAs($customer, 'customer')->post(route('customer.complaints.store'), [
            'order_id' => $order->id,
            'subject' => 'Article non conforme',
            'message' => 'La couleur reçue ne correspond pas à la photo.',
        ]);
        $complaintResponse->assertSessionHasNoErrors();

        $complaint = Complaint::where('customer_id', $customer->id)->firstOrFail();

        // --- Réponse + remboursement (admin) ---
        $this->actingAs($admin)
            ->post(route('complaints.reply', $complaint), ['admin_reply' => 'Nous vous remboursons intégralement, désolés pour la gêne.'])
            ->assertRedirect();
        Notification::assertSentTo($customer, ComplaintReplyNotification::class);

        $refundResponse = $this->actingAs($admin)->post(route('complaints.refund', $complaint), [
            'reason' => 'non_conforme',
            'amount' => 100.00,
            'method' => 'especes',
            'note' => 'Remboursé en espèces au comptoir.',
        ]);
        $refundResponse->assertSessionHasNoErrors();
        $refundResponse->assertRedirect();

        $refund = Refund::where('complaint_id', $complaint->id)->firstOrFail();
        $this->assertSame(10000, $refund->amount);
        $this->assertSame('resolu', $complaint->fresh()->status);

        // Un second remboursement sur la même réclamation doit être refusé.
        $secondAttempt = $this->actingAs($admin)->post(route('complaints.refund', $complaint), [
            'reason' => 'non_conforme',
            'amount' => 10.00,
        ]);
        $secondAttempt->assertSessionHas('error');
        $this->assertSame(1, Refund::where('complaint_id', $complaint->id)->count());

        // --- Le module Finance doit rendre sans erreur avec ces données réelles ---
        $this->actingAs($admin)->get(route('finance'))->assertOk();
        $this->actingAs($admin)->get(route('finance.report'))->assertOk();
        $this->actingAs($admin)->get(route('finance.export'))->assertOk();
    }

    public function test_customer_cannot_access_another_customers_order(): void
    {
        $owner = Customer::factory()->create();
        $stranger = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $owner->id]);

        $this->actingAs($stranger, 'customer')
            ->get(route('shop.orders.tracking', $order->order_number))
            ->assertForbidden();
    }

    public function test_pos_manual_sale_creates_order_and_decrements_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['status' => 'active', 'price' => 3000]);
        $variant = $product->variants()->first();
        $variant->update(['stock_quantity' => 5]);

        // customer_phone volontairement omis : une vendeuse peut enregistrer une
        // vente comptoir sans relever les coordonnées de la cliente.
        $response = $this->actingAs($admin)->post(route('pos.store'), [
            'customer_name' => 'Client comptoir',
            'payment_method' => 'especes',
            'items' => [
                ['product_variant_id' => $variant->id, 'quantity' => 2],
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $order = Order::where('channel', 'vente_manuelle')->latest()->firstOrFail();
        $this->assertSame(6000, $order->total);
        $this->assertSame(3, $variant->fresh()->stock_quantity);
    }
}
