<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductVariation;
use App\Services\WooCommerceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function __construct(private WooCommerceService $wooCommerce)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $orders = Order::with(['customer', 'items.variation', 'payment'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('to')))
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return response()->json($orders);
    }

    public function store(OrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        $order = DB::transaction(function () use ($data) {
            $customer = $this->resolveCustomer($data['customer'] ?? []);

            $order = Order::create([
                'order_number' => Str::upper(Str::random(10)),
                'customer_id' => $customer?->id,
                'status' => 'completed',
                'subtotal' => 0,
                'discount' => 0,
                'tax' => 0,
                'total' => 0,
                'payment_method' => $data['payment']['method'],
                'paid_at' => Carbon::now(),
                'source' => 'pos',
                'notes' => $data['notes'] ?? null,
            ]);

            $subtotal = 0;
            $taxTotal = 0;
            $discountTotal = 0;

            foreach ($data['items'] as $itemData) {
                /** @var ProductVariation $variation */
                $variation = ProductVariation::lockForUpdate()->findOrFail($itemData['product_variation_id']);

                $unitPrice = $variation->price ?: $variation->product->base_price;
                $lineSubtotal = $unitPrice * $itemData['quantity'];
                $lineDiscount = $itemData['discount'] ?? 0;
                $lineTax = ($variation->product->tax_rate ?? 0) * $lineSubtotal / 100;

                $subtotal += $lineSubtotal;
                $discountTotal += $lineDiscount;
                $taxTotal += $lineTax;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $variation->product_id,
                    'product_variation_id' => $variation->id,
                    'name' => $variation->product->name,
                    'color' => $variation->color,
                    'size' => $variation->size,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $unitPrice,
                    'total' => $lineSubtotal - $lineDiscount + $lineTax,
                    'sku' => $variation->sku,
                ]);

                $variation->decrement('stock', $itemData['quantity']);
            }

            $order->update([
                'subtotal' => $subtotal,
                'discount' => $discountTotal,
                'tax' => $taxTotal,
                'total' => $subtotal - $discountTotal + $taxTotal,
            ]);

            Payment::create([
                'order_id' => $order->id,
                'method' => $data['payment']['method'],
                'amount' => $data['payment']['amount'],
                'paid_at' => Carbon::now(),
            ]);

            return $order->load(['customer', 'items', 'payment']);
        });

        if ($request->boolean('sync_to_woocommerce')) {
            $this->wooCommerce->pushOrder($order);
        }

        return response()->json($order, 201);
    }

    public function importFromWooCommerce(): JsonResponse
    {
        $orders = $this->wooCommerce->importOrders();

        return response()->json(['imported' => $orders]);
    }

    protected function resolveCustomer(array $data): ?Customer
    {
        if (empty($data)) {
            return null;
        }

        if (! empty($data['id'])) {
            return Customer::findOrFail($data['id']);
        }

        return Customer::updateOrCreate(
            ['email' => $data['email'] ?? null, 'phone' => $data['phone'] ?? null],
            [
                'name' => $data['name'],
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'country' => $data['country'] ?? null,
            ]
        );
    }
}
