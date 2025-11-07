<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WooCommerceService
{
    protected string $baseUrl;
    protected string $consumerKey;
    protected string $consumerSecret;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.woocommerce.url'), '/');
        $this->consumerKey = config('services.woocommerce.consumer_key');
        $this->consumerSecret = config('services.woocommerce.consumer_secret');
    }

    public function syncProducts(): array
    {
        if (empty($this->baseUrl)) {
            return ['status' => 'skipped', 'reason' => 'woocommerce url missing'];
        }

        $response = $this->client()->get('/wp-json/wc/v3/products', [
            'per_page' => 100,
        ]);

        if ($response->failed()) {
            Log::error('WooCommerce product sync failed', ['response' => $response->json()]);
            return ['status' => 'error'];
        }

        $products = [];
        foreach ($response->json() as $payload) {
            $product = Product::updateOrCreate(
                ['name' => $payload['name']],
                [
                    'category' => $payload['categories'][0]['name'] ?? 'Uncategorized',
                    'brand' => $payload['attributes'][0]['options'][0] ?? null,
                    'description' => strip_tags($payload['description'] ?? ''),
                    'base_price' => $payload['price'] ?? 0,
                    'is_active' => $payload['status'] === 'publish',
                ]
            );

            foreach ($payload['variations'] ?? [] as $variationPayload) {
                ProductVariation::updateOrCreate(
                    ['sku' => $variationPayload['sku'] ?: Str::uuid()],
                    [
                        'product_id' => $product->id,
                        'color' => $this->attributeValue($variationPayload, 'Color'),
                        'size' => $this->attributeValue($variationPayload, 'Size'),
                        'barcode' => data_get($variationPayload, 'meta_data.barcode'),
                        'price' => $variationPayload['regular_price'] ?? $product->base_price,
                        'stock' => $variationPayload['stock_quantity'] ?? 0,
                    ]
                );
            }

            $products[] = $product->id;
        }

        return ['status' => 'ok', 'products' => $products];
    }

    public function syncInventory(): array
    {
        if (! $this->baseUrl) {
            return ['status' => 'skipped'];
        }

        $variations = ProductVariation::with('product')->get();
        $updates = [];

        foreach ($variations as $variation) {
            if (! $variation->sku) {
                continue;
            }

            $payload = [
                'stock_quantity' => $variation->stock,
                'regular_price' => $variation->price ?? $variation->product->base_price,
            ];

            $response = $this->client()->put(
                sprintf('/wp-json/wc/v3/products/%d/variations/%s', $variation->product_id, $variation->sku),
                $payload
            );

            if ($response->ok()) {
                $updates[] = $variation->sku;
            }
        }

        return ['status' => 'ok', 'updated' => $updates];
    }

    public function importOrders(): array
    {
        if (! $this->baseUrl) {
            return [];
        }

        $response = $this->client()->get('/wp-json/wc/v3/orders', [
            'per_page' => 50,
            'status' => 'processing,completed',
        ]);

        if ($response->failed()) {
            Log::error('Failed importing WooCommerce orders', ['response' => $response->json()]);
            return [];
        }

        $imported = [];
        foreach ($response->json() as $orderPayload) {
            $customer = Customer::updateOrCreate(
                ['woocommerce_id' => $orderPayload['customer_id']],
                [
                    'name' => $orderPayload['billing']['first_name'].' '.$orderPayload['billing']['last_name'],
                    'email' => $orderPayload['billing']['email'],
                    'phone' => $orderPayload['billing']['phone'],
                    'address' => $orderPayload['billing']['address_1'],
                    'city' => $orderPayload['billing']['city'],
                    'state' => $orderPayload['billing']['state'],
                    'postal_code' => $orderPayload['billing']['postcode'],
                    'country' => $orderPayload['billing']['country'],
                ]
            );

            $order = Order::updateOrCreate(
                ['woocommerce_id' => $orderPayload['id']],
                [
                    'order_number' => $orderPayload['number'],
                    'customer_id' => $customer->id,
                    'status' => $orderPayload['status'],
                    'subtotal' => $orderPayload['total'] - $orderPayload['total_tax'],
                    'discount' => $orderPayload['discount_total'],
                    'tax' => $orderPayload['total_tax'],
                    'total' => $orderPayload['total'],
                    'payment_method' => $orderPayload['payment_method'],
                    'paid_at' => Carbon::parse($orderPayload['date_paid'] ?? $orderPayload['date_created']),
                    'source' => 'web',
                    'synced_at' => Carbon::now(),
                ]
            );

            $order->items()->delete();
            foreach ($orderPayload['line_items'] as $item) {
                $variation = ProductVariation::where('sku', $item['sku'])->first();
                $order->items()->create([
                    'product_id' => $variation?->product_id,
                    'product_variation_id' => $variation?->id,
                    'name' => $item['name'],
                    'color' => $variation?->color,
                    'size' => $variation?->size,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total' => $item['total'],
                    'sku' => $item['sku'],
                ]);
            }

            $imported[] = $order->id;
        }

        return $imported;
    }

    public function pushOrder(Order $order): void
    {
        if (! $this->baseUrl) {
            return;
        }

        $payload = [
            'status' => 'completed',
            'payment_method' => $order->payment_method,
            'set_paid' => true,
            'billing' => [
                'first_name' => optional($order->customer)->name,
                'email' => optional($order->customer)->email,
                'phone' => optional($order->customer)->phone,
                'address_1' => optional($order->customer)->address,
                'city' => optional($order->customer)->city,
                'state' => optional($order->customer)->state,
                'postcode' => optional($order->customer)->postal_code,
                'country' => optional($order->customer)->country,
            ],
            'line_items' => $order->items->map(fn ($item) => [
                'sku' => $item->sku,
                'quantity' => $item->quantity,
                'price' => $item->unit_price,
            ])->toArray(),
        ];

        $response = $this->client()->post('/wp-json/wc/v3/orders', $payload);

        if ($response->successful()) {
            $order->update([
                'woocommerce_id' => $response->json('id'),
                'synced_at' => Carbon::now(),
            ]);
        } else {
            Log::warning('Unable to push order to WooCommerce', ['order' => $order->id, 'response' => $response->json()]);
        }
    }

    public function syncSingleProduct(Product $product): array
    {
        if (! $this->baseUrl) {
            return ['status' => 'skipped'];
        }

        $payload = [
            'name' => $product->name,
            'description' => $product->description,
            'regular_price' => $product->base_price,
            'categories' => [
                ['name' => $product->category],
            ],
            'attributes' => [
                ['name' => 'Brand', 'options' => array_filter([$product->brand])],
            ],
        ];

        $response = $this->client()->post('/wp-json/wc/v3/products', $payload);

        if ($response->failed()) {
            Log::error('Failed syncing product', ['product' => $product->id, 'response' => $response->json()]);
            return ['status' => 'error'];
        }

        return $response->json();
    }

    protected function client()
    {
        return Http::baseUrl($this->baseUrl)
            ->withBasicAuth($this->consumerKey, $this->consumerSecret)
            ->acceptJson();
    }

    protected function attributeValue(array $variationPayload, string $attribute): ?string
    {
        foreach ($variationPayload['attributes'] ?? [] as $attr) {
            if (($attr['name'] ?? '') === $attribute) {
                return $attr['option'] ?? null;
            }
        }

        return null;
    }
}
