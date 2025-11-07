<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use WithFaker;

    public function test_authenticated_user_can_list_products(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create();
        $product->variations()->create(
            ProductVariation::factory()->make()->toArray()
        );

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/products');

        $response->assertOk()->assertJsonFragment([
            'name' => $product->name,
        ]);
    }
}
