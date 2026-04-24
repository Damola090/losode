<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Vendor;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_products_endpoint_returns_paginated_results(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'data',
                    'current_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    public function test_public_products_endpoint_supports_search(): void
    {
        $vendor = Vendor::where('email', 'adunni@losode.test')->firstOrFail();
        Product::factory()->create([
            'vendor_id' => $vendor->id,
            'name' => 'Ankara Crown Dress',
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/v1/products?search=Ankara');

        $response->assertOk()
            ->assertJsonFragment(['name' => 'Ankara Crown Dress']);
    }

    public function test_public_product_detail_returns_not_found_for_inactive_product(): void
    {
        $vendor = Vendor::where('email', 'adunni@losode.test')->firstOrFail();
        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'status' => 'inactive',
        ]);

        $this->getJson("/api/v1/products/{$product->id}")
            ->assertNotFound()
            ->assertJsonPath('message', 'Product not found.');
    }

    public function test_authenticated_vendor_can_create_a_product(): void
    {
        $vendor = Vendor::where('email', 'adunni@losode.test')->firstOrFail();

        $response = $this->withToken($vendor->createToken('test-token')->plainTextToken)
            ->postJson('/api/v1/vendor/products', [
                'name' => 'Midnight Adire',
                'description' => 'Hand-dyed adire two-piece set.',
                'price' => 199.99,
                'stock_quantity' => 12,
                'status' => 'active',
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.product.vendor_id', $vendor->id)
            ->assertJsonPath('data.product.name', 'Midnight Adire');

        $this->assertDatabaseHas('products', [
            'vendor_id' => $vendor->id,
            'name' => 'Midnight Adire',
        ]);
    }

    public function test_vendor_cannot_update_another_vendors_product(): void
    {
        $owner = Vendor::where('email', 'adunni@losode.test')->firstOrFail();
        $intruder = Vendor::where('email', 'kola@losode.test')->firstOrFail();
        $product = Product::where('vendor_id', $owner->id)->firstOrFail();

        $response = $this->withToken($intruder->createToken('test-token')->plainTextToken)
            ->putJson("/api/v1/vendor/products/{$product->id}", [
                'name' => 'Hijacked Listing',
            ]);

        $response->assertForbidden()
            ->assertJsonPath('message', 'You do not own this product.');
    }

    public function test_vendor_can_delete_owned_product(): void
    {
        $vendor = Vendor::where('email', 'adunni@losode.test')->firstOrFail();
        $product = Product::where('vendor_id', $vendor->id)->firstOrFail();

        $response = $this->withToken($vendor->createToken('test-token')->plainTextToken)
            ->deleteJson("/api/v1/vendor/products/{$product->id}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
}
