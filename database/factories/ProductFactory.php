<?php

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vendor_id'      => Vendor::factory(),
            'name'           => fake()->words(3, true),
            'description'    => fake()->paragraph(),
            'price'          => fake()->randomFloat(2, 5, 500),
            'stock_quantity' => fake()->numberBetween(0, 200),
            'status'         => fake()->randomElement(['active', 'active', 'inactive']), // weighted active
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function outOfStock(): static
    {
        return $this->state(['stock_quantity' => 0]);
    }
}
