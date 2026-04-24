<?php

namespace Tests\Feature;

use App\Models\Vendor;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_vendor_can_register_with_password_confirmation(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Funke Fabrics',
            'email' => 'funke@example.com',
            'password' => 'secretpass123',
            'password_confirmation' => 'secretpass123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.vendor.email', 'funke@example.com')
            ->assertJsonStructure([
                'data' => [
                    'vendor' => ['id', 'name', 'email'],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('vendors', [
            'email' => 'funke@example.com',
        ]);
    }

    public function test_registration_requires_matching_password_confirmation(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Funke Fabrics',
            'email' => 'funke@example.com',
            'password' => 'secretpass123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_vendor_can_login_and_receive_a_token(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'adunni@losode.test',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.vendor.email', 'adunni@losode.test')
            ->assertJsonStructure([
                'data' => [
                    'vendor' => ['id', 'name', 'email'],
                    'token',
                ],
            ]);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'adunni@losode.test',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_vendor_can_fetch_profile(): void
    {
        $vendor = Vendor::where('email', 'adunni@losode.test')->firstOrFail();

        $response = $this->withToken($vendor->createToken('test-token')->plainTextToken)
            ->getJson('/api/v1/me');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.vendor.email', 'adunni@losode.test');
    }

    public function test_profile_requires_authentication(): void
    {
        $this->getJson('/api/v1/me')
            ->assertUnauthorized();
    }
}
