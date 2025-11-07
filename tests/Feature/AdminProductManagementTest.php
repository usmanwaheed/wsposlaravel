<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminProductManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsAdmin(): User
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);

        return $admin;
    }

    public function test_admin_can_create_product_with_variations(): void
    {
        $this->actingAsAdmin();

        $payload = [
            'name' => 'Tailored Shirt',
            'category' => 'Shirts',
            'brand' => 'TailorMade',
            'description' => 'Premium cotton shirt.',
            'base_price' => 35,
            'tax_rate' => 8,
            'variations' => [
                [
                    'color' => 'Blue',
                    'size' => 'M',
                    'sku' => 'TSHIRT-BLU-M',
                    'barcode' => '123456789012',
                    'price' => 37.5,
                    'stock' => 12,
                ],
                [
                    'color' => 'Blue',
                    'size' => 'L',
                    'sku' => 'TSHIRT-BLU-L',
                    'barcode' => '123456789013',
                    'price' => '',
                    'stock' => 8,
                ],
            ],
        ];

        $response = $this->post(route('products.store'), $payload);

        $product = Product::first();

        $response->assertRedirect(route('products.edit', $product));
        $this->assertDatabaseHas('products', ['name' => 'Tailored Shirt', 'category' => 'Shirts']);
        $this->assertDatabaseCount('product_variations', 2);
    }

    public function test_admin_can_update_product_and_variations(): void
    {
        $this->actingAsAdmin();

        $product = Product::factory()->create([
            'name' => 'Classic Jeans',
            'base_price' => 45,
            'tax_rate' => 5,
        ]);

        $variation = $product->variations()->create([
            'color' => 'Indigo',
            'size' => '32',
            'sku' => Str::upper(Str::random(10)),
            'barcode' => '555555555555',
            'price' => 45,
            'stock' => 10,
        ]);

        $response = $this->put(route('products.update', $product), [
            'name' => 'Classic Jeans Updated',
            'category' => $product->category,
            'brand' => $product->brand,
            'description' => $product->description,
            'base_price' => 47,
            'tax_rate' => 6,
            'variations' => [
                [
                    'id' => $variation->id,
                    'color' => 'Indigo',
                    'size' => '32',
                    'sku' => $variation->sku,
                    'barcode' => $variation->barcode,
                    'price' => 47,
                    'stock' => 18,
                ],
                [
                    'color' => 'Black',
                    'size' => '34',
                    'sku' => 'JEANS-BLK-34',
                    'barcode' => '444444444444',
                    'price' => 49,
                    'stock' => 6,
                ],
            ],
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Classic Jeans Updated', 'base_price' => 47]);
        $this->assertDatabaseHas('product_variations', ['id' => $variation->id, 'stock' => 18]);
        $this->assertDatabaseHas('product_variations', ['sku' => 'JEANS-BLK-34']);
    }

    public function test_inventory_add_mode_creates_purchase_order(): void
    {
        $this->actingAsAdmin();

        $product = Product::factory()->create(['base_price' => 30]);
        $variation = $product->variations()->create([
            'color' => 'Red',
            'size' => 'S',
            'sku' => 'TEE-RED-S',
            'barcode' => '111111111111',
            'price' => 32,
            'stock' => 4,
        ]);

        $response = $this->patch(route('inventory.update', $variation), [
            'mode' => 'add',
            'quantity' => 6,
            'notes' => 'Supplier restock',
        ]);

        $response->assertRedirect();
        $this->assertSame(10, $variation->fresh()->stock);
        $this->assertDatabaseHas('orders', ['source' => 'purchase', 'notes' => 'Supplier restock']);
        $this->assertDatabaseHas('order_items', ['product_variation_id' => $variation->id, 'quantity' => 6]);
    }
}

