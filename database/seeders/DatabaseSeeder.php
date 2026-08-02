<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Label;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PromoCode;
use App\Models\Review;
use App\Models\StoreSetting;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin Boutique',
            'email' => 'admin@boutique.test',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        User::factory()->create([
            'name' => 'Marie Vendeuse',
            'email' => 'vendeuse@boutique.test',
            'role' => 'vendeur',
            'password' => bcrypt('password'),
        ]);

        StoreSetting::create([
            'name' => 'Boutique Back-Office',
            'description' => "Boutique en ligne de vêtements et accessoires, pensée pour l'achat depuis mobile.",
        ]);

        collect(['Femme', 'Homme', 'Accessoires', 'Nouveautés'])
            ->each(fn ($name) => Category::create(['name' => $name]));

        collect(['Nouveauté', 'Coup de cœur', 'Édition limitée', 'Best-seller'])
            ->each(fn ($name) => Label::create(['name' => $name]));

        collect(['coton bio', 'été', 'oversize', 'cuir', 'fait main', 'recyclé'])
            ->each(fn ($name) => Tag::create(['name' => $name]));

        Product::factory(48)->create()->each(function (Product $product) {
            $product->labels()->attach(Label::inRandomOrder()->take(random_int(0, 2))->pluck('id'));
            $product->tags()->attach(Tag::inRandomOrder()->take(random_int(0, 3))->pluck('id'));
        });

        $this->seedOrders();
        $this->seedExpenses();
        $this->seedReviews();
        $this->seedPromoCodes();
    }

    private function seedOrders(): void
    {
        $variants = ProductVariant::with('product')->get();
        $flow = Order::FLOW; // en_attente, confirmee, preparee, expediee, livree
        $timestampFields = [
            'confirmee' => 'confirmed_at',
            'preparee' => 'prepared_at',
            'expediee' => 'shipped_at',
            'livree' => 'delivered_at',
        ];

        // Pool de clients pondéré : quelques habitués (VIP), quelques réguliers,
        // le reste n'achète qu'une fois — pour des segments Clients réalistes.
        $customers = Customer::factory(20)->create();
        $weightedCustomers = collect();
        $customers->slice(0, 3)->each(fn ($c) => $weightedCustomers = $weightedCustomers->concat(array_fill(0, 6, $c)));
        $customers->slice(3, 6)->each(fn ($c) => $weightedCustomers = $weightedCustomers->concat(array_fill(0, 2, $c)));
        $customers->slice(9)->each(fn ($c) => $weightedCustomers->push($c));

        // Distribution volontairement réaliste : surtout des commandes traitées,
        // quelques-unes bloquées en attente/vérification, une poignée d'annulées.
        $statusPool = [
            ...array_fill(0, 3, 'en_attente'),
            ...array_fill(0, 4, 'confirmee'),
            ...array_fill(0, 3, 'preparee'),
            ...array_fill(0, 4, 'expediee'),
            ...array_fill(0, 10, 'livree'),
            ...array_fill(0, 3, 'annulee'),
        ];

        foreach ($statusPool as $targetStatus) {
            // Étalé sur ~55 jours pour peupler à la fois "ce mois-ci" et "mois dernier" en Finance.
            $placedAt = Carbon::now()->subDays(random_int(0, 55))->subHours(random_int(0, 23));
            $customer = $weightedCustomers->random();

            $order = Order::factory()->create([
                'status' => $targetStatus === 'annulee' ? 'annulee' : $targetStatus,
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_address' => $customer->address,
                'created_at' => $placedAt,
                'updated_at' => $placedAt,
            ]);

            // Horodatage cohérent : chaque étape franchie a une date après la précédente.
            $cursor = $placedAt->copy();
            if ($targetStatus === 'annulee') {
                $order->cancelled_at = $cursor->addHours(random_int(1, 12));
                $order->payment_status = fake()->randomElement(['en_attente_verification', 'rejete']);
            } else {
                $reachedIndex = array_search($targetStatus, $flow, true);
                foreach ($flow as $index => $step) {
                    if ($index === 0 || $index > $reachedIndex) {
                        continue;
                    }
                    $cursor = $cursor->copy()->addHours(random_int(3, 30));
                    $order->{$timestampFields[$step]} = $cursor;
                }
                // Une commande qui a avancé au-delà de la vérification a forcément été payée.
                $order->payment_status = $targetStatus === 'en_attente' && fake()->boolean(40)
                    ? 'en_attente_verification'
                    : 'verifie';
            }

            // Articles de la commande, avec snapshot du prix au moment de l'achat.
            $total = 0;
            foreach ($variants->random(random_int(1, 4)) as $variant) {
                $product = $variant->product;
                $quantity = random_int(1, 3);
                $subtotal = $product->price * $quantity;
                $total += $subtotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $product->name,
                    'variant_label' => $variant->label(),
                    'unit_price' => $product->price,
                    'unit_cost' => $product->cost_price ?? 0,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ]);
            }

            $order->total = $total;
            $order->save();
        }
    }

    private function seedExpenses(): void
    {
        $samples = [
            ['category' => 'livraison', 'label' => 'Livraison Jumia Express', 'amount' => random_int(8000, 25000)],
            ['category' => 'livraison', 'label' => 'Transport fournisseur', 'amount' => random_int(5000, 15000)],
            ['category' => 'hebergement', 'label' => 'Hébergement site web', 'amount' => random_int(3000, 8000)],
            ['category' => 'marketing', 'label' => 'Publicité Instagram', 'amount' => random_int(10000, 40000)],
            ['category' => 'marketing', 'label' => 'Impression flyers', 'amount' => random_int(5000, 12000)],
            ['category' => 'commissions', 'label' => 'Commissions Orange Money', 'amount' => random_int(4000, 9000)],
            ['category' => 'commissions', 'label' => 'Commissions Wave', 'amount' => random_int(3000, 7000)],
            ['category' => 'salaires', 'label' => 'Salaire vendeuse', 'amount' => random_int(80000, 120000)],
            ['category' => 'autre', 'label' => 'Fournitures emballage', 'amount' => random_int(4000, 10000)],
        ];

        foreach ($samples as $sample) {
            Expense::create([
                ...$sample,
                'expense_date' => Carbon::now()->subDays(random_int(0, 55)),
                'user_id' => User::inRandomOrder()->value('id'),
            ]);
        }
    }

    private function seedReviews(): void
    {
        // Avis "achat vérifié" : rattachés à une ligne de commande réelle,
        // donc client/produit/commande restent cohérents entre eux.
        OrderItem::with('order.customer')
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'verifie'))
            ->inRandomOrder()
            ->limit(18)
            ->get()
            ->unique(fn ($item) => $item->order->customer_id . '-' . $item->product_id)
            ->each(function (OrderItem $item) {
                Review::factory()->create([
                    'product_id' => $item->product_id,
                    'customer_id' => $item->order->customer_id,
                    'order_id' => $item->order_id,
                    'author_name' => $item->order->customer_name,
                    'created_at' => $item->order->created_at->addDays(random_int(1, 6)),
                ]);
            });

        // Avis "libres" : transcrits depuis WhatsApp/réseaux, sans commande rattachée.
        Product::inRandomOrder()->limit(10)->get()->each(function (Product $product) {
            Review::factory()->create([
                'product_id' => $product->id,
                'created_at' => Carbon::now()->subDays(random_int(0, 40)),
            ]);
        });

        $replies = [
            'Merci beaucoup pour votre retour, ça nous fait très plaisir !',
            'Merci pour votre confiance, à très vite pour une prochaine commande !',
            "Nous sommes désolés pour ce désagrément, n'hésitez pas à nous contacter pour un échange.",
        ];

        Review::where('status', 'publie')->inRandomOrder()->limit(8)->get()->each(function (Review $review) use ($replies) {
            $review->update([
                'admin_reply' => fake()->randomElement($replies),
                'replied_at' => $review->created_at->addDays(random_int(1, 3)),
            ]);
        });
    }

    private function seedPromoCodes(): void
    {
        PromoCode::create([
            'code' => 'BIENVENUE10',
            'type' => 'pourcentage',
            'value' => 10,
            'used_count' => 14,
            'is_active' => true,
        ]);

        PromoCode::create([
            'code' => 'SOLDES20',
            'type' => 'pourcentage',
            'value' => 20,
            'max_uses' => 50,
            'used_count' => 22,
            'expires_at' => Carbon::now()->addDays(15),
            'is_active' => true,
        ]);

        PromoCode::create([
            'code' => 'LIVRAISON',
            'type' => 'montant_fixe',
            'value' => 2000, // 20 €
            'min_order_amount' => 15000, // 150 €
            'used_count' => 6,
            'is_active' => true,
        ]);

        PromoCode::create([
            'code' => 'VIP15',
            'type' => 'pourcentage',
            'value' => 15,
            'used_count' => 3,
            'is_active' => false,
        ]);

        PromoCode::create([
            'code' => 'ETE2026',
            'type' => 'pourcentage',
            'value' => 15,
            'used_count' => 31,
            'expires_at' => Carbon::now()->subDays(5),
            'is_active' => true,
        ]);
    }
}
