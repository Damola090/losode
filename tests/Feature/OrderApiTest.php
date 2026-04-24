<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Vendor;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_can_place_an_order_for_an_active_product(): void
    {
        $product = Product::query()->where('status', 'active')->firstOrFail();
        $startingStock = $product->stock_quantity;

        $response = $this->postJson('/api/v1/orders', [
            'product_id' => $product->id,
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'quantity' => 2,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order.product_id', $product->id)
            ->assertJsonPath('data.order.quantity', 2)
            ->assertJsonPath('data.order.status', 'confirmed');

        $this->assertDatabaseHas('orders', [
            'product_id' => $product->id,
            'customer_email' => 'jane@example.com',
            'quantity' => 2,
        ]);

        $this->assertSame($startingStock - 2, $product->fresh()->stock_quantity);
    }

    public function test_order_fails_when_quantity_exceeds_available_stock(): void
    {
        $vendor = Vendor::where('email', 'adunni@losode.test')->firstOrFail();
        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'status' => 'active',
            'stock_quantity' => 1,
        ]);

        $response = $this->postJson('/api/v1/orders', [
            'product_id' => $product->id,
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'quantity' => 2,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Insufficient stock. Available: 1.');
    }

    public function test_order_fails_for_inactive_product(): void
    {
        $vendor = Vendor::where('email', 'adunni@losode.test')->firstOrFail();
        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'status' => 'inactive',
        ]);

        $response = $this->postJson('/api/v1/orders', [
            'product_id' => $product->id,
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'quantity' => 1,
        ]);

        $response->assertNotFound()
            ->assertJsonPath('message', 'Product not found.');
    }
}
