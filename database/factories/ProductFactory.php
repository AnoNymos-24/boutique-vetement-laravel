<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected static array $names = [
        'Robe fluide imprimée', 'Chemise oversize lin', 'Jean taille haute', 'Blazer structuré',
        'Sac bandoulière cuir', 'Boucles d\'oreilles dorées', 'Sneakers blanches', 'Pull col rond cachemire',
        'Jupe plissée midi', 'Trench coat beige', 'T-shirt basique coton bio', 'Ceinture cuir tressé',
        'Manteau laine oversize', 'Foulard soie imprimé', 'Sandales à talon', 'Legging sport technique',
    ];

    protected static array $accessoryNames = [
        'Sac bandoulière cuir', 'Boucles d\'oreilles dorées', 'Ceinture cuir tressé', 'Foulard soie imprimé',
    ];

    protected static array $shoeNames = ['Sneakers blanches', 'Sandales à talon'];

    public function definition(): array
    {
        $price = fake()->numberBetween(1500, 24000);
        $hasPromo = fake()->boolean(25);

        return [
            'name' => fake()->randomElement(self::$names),
            'category_id' => Category::inRandomOrder()->value('id'),
            'description' => fake()->paragraph(3),
            'price' => $price,
            // Coût d'achat ~45-65% du prix de vente, pour une marge réaliste.
            'cost_price' => (int) round($price * fake()->randomFloat(2, 0.45, 0.65)),
            'compare_at_price' => $hasPromo ? (int) round($price * 1.25) : null,
            'status' => fake()->randomElement(['active', 'active', 'active', 'draft']),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            $this->createVariants($product);
        });
    }

    private function createVariants(Product $product): void
    {
        $color = fake()->randomElement(['Noir', 'Blanc', 'Beige', 'Bordeaux', 'Bleu marine', 'Kaki']);

        if (in_array($product->name, self::$accessoryNames, true)) {
            $this->makeVariant($product, null, $color);

            return;
        }

        if (in_array($product->name, self::$shoeNames, true)) {
            $sizes = fake()->randomElements(['37', '38', '39', '40', '41'], random_int(2, 4));
            foreach ($sizes as $size) {
                $this->makeVariant($product, $size, $color);
            }

            return;
        }

        $sizes = fake()->randomElements(['XS', 'S', 'M', 'L', 'XL'], random_int(2, 4));
        foreach ($sizes as $size) {
            $this->makeVariant($product, $size, $color);
        }
    }

    private function makeVariant(Product $product, ?string $size, ?string $color): void
    {
        $product->variants()->create([
            'size' => $size,
            'color' => $color,
            'sku' => strtoupper(fake()->unique()->bothify('BTQ-####-??')),
            'stock_quantity' => fake()->numberBetween(0, 40),
            'low_stock_threshold' => 5,
        ]);
    }
}
