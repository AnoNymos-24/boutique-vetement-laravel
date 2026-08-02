<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->unique()->numerify('07########'),
            'email' => fake()->boolean(40) ? fake()->unique()->safeEmail() : null,
            'address' => fake()->boolean(60) ? fake()->address() : null,
        ];
    }
}
