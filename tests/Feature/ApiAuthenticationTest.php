<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_exchange_credentials_for_token(): void
    {
        $user = User::factory()->create([
            'email' => 'api-user@example.com',
            'password' => bcrypt('secret-password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'api-user@example.com',
            'password' => 'secret-password',
            'device_name' => 'integration-tests',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'token_type',
                'user' => ['id', 'name', 'email', 'role'],
            ]);
    }

    public function test_protected_routes_require_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'protected@example.com',
            'password' => bcrypt('top-secret'),
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $product = Product::factory()->create();
        ProductVariation::factory()->create([
            'product_id' => $product->id,
        ]);

        $response = $this->withToken($token)->getJson('/api/products');

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_login_returns_error_for_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'invalid@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
