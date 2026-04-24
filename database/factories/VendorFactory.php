<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class VendorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'     => fake()->company(),
            'email'    => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
        ];
    }
}
