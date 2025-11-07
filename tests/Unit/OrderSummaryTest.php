<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductVariation;
use Tests\TestCase;

class OrderSummaryTest extends TestCase
{
    public function test_product_variation_price_defaults_to_product_base_price(): void
    {
        $product = Product::factory()->create(['base_price' => 25.50]);
        $variation = $product->variations()->create(
            ProductVariation::factory()->make(['price' => null])->toArray()
        );

        $this->assertNull($variation->price);
        $this->assertEquals($product->base_price, $variation->product->base_price);
    }
}
