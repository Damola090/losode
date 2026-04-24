<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed 2 known vendors for easy testing
        $vendor1 = Vendor::updateOrCreate(
            ['email' => 'adunni@losode.test'],
            [
                'name'     => 'Adunni Styles',
                'password' => Hash::make('password'),
            ]
        );

        $vendor2 = Vendor::updateOrCreate(
            ['email' => 'kola@losode.test'],
            [
                'name'     => 'Kola Crafts',
                'password' => Hash::make('password'),
            ]
        );

        // Keep a predictable baseline set of products for the known vendors.
        if ($vendor1->products()->count() === 0) {
            Product::factory()->count(8)->create(['vendor_id' => $vendor1->id]);
        }

        if ($vendor2->products()->count() === 0) {
            Product::factory()->count(8)->create(['vendor_id' => $vendor2->id]);
        }

        // Extra random vendors
        if (Vendor::whereNotIn('email', ['adunni@losode.test', 'kola@losode.test'])->count() === 0) {
            Vendor::factory()
                ->count(3)
                ->has(Product::factory()->count(5))
                ->create();
        }
    }
}
