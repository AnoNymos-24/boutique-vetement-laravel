<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_number' => 'CMD-' . strtoupper(fake()->unique()->bothify('??####')),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->numerify('07########'),
            'customer_address' => fake()->boolean(70) ? fake()->address() : null,
            'status' => 'en_attente',
            'payment_status' => 'en_attente_verification',
            'payment_method' => fake()->randomElement(['orange_money', 'wave']),
            'payment_reference' => fake()->numerify('######'),
            'total' => 0,
            'note' => fake()->boolean(15) ? fake()->sentence() : null,
        ];
    }
}
