<?php

namespace Database\Factories;

use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected static array $comments = [
        5 => [
            'Exactement comme sur les photos, matière superbe. Je recommande !',
            'Livraison rapide et produit conforme, je suis ravie.',
            'Coup de cœur total, la coupe est parfaite.',
            'Qualité au rendez-vous, je recommande cette boutique les yeux fermés.',
        ],
        4 => [
            'Très joli produit, juste un peu long à recevoir.',
            'Belle qualité, la taille conseillée était juste un peu grande.',
            'Content de mon achat, correspond à la description.',
        ],
        3 => [
            'Correct sans plus, la matière est un peu fine.',
            'Produit conforme mais la couleur diffère légèrement des photos.',
        ],
        2 => [
            'Déçue par la qualité de la couture.',
            'La taille ne correspondait pas du tout, dommage.',
        ],
        1 => [
            'Produit reçu abîmé, je n\'ai pas pu le porter.',
        ],
    ];

    public function definition(): array
    {
        $rating = fake()->randomElement([5, 5, 5, 4, 4, 4, 3, 3, 2, 1]);

        return [
            'author_name' => fake()->firstName() . ' ' . strtoupper(substr(fake()->lastName(), 0, 1)) . '.',
            'rating' => $rating,
            'comment' => fake()->randomElement(self::$comments[$rating]),
            'status' => fake()->randomElement(['publie', 'publie', 'publie', 'en_attente']),
        ];
    }
}
