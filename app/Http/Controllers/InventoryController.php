<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockUpdateRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manage-products');
    }

    public function index(): View
    {
        $variations = ProductVariation::with('product')->orderByDesc('updated_at')->paginate(20);
        $lowStock = ProductVariation::with('product')->where('stock', '<', 5)->orderBy('stock')->get();

        return view('inventory.index', [
            'variations' => $variations,
            'lowStock' => $lowStock,
        ]);
    }

    public function update(StockUpdateRequest $request, ProductVariation $variation): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($variation, $data) {
            if ($data['mode'] === 'add') {
                $variation->increment('stock', $data['quantity']);

                $unitPrice = $variation->price ?: $variation->product->base_price;
                $lineTotal = $unitPrice * $data['quantity'];

                /** @var Order $order */
                $order = Order::create([
                    'order_number' => 'PO-' . Str::upper(Str::random(8)),
                    'status' => 'received',
                    'subtotal' => $lineTotal,
                    'discount' => 0,
                    'tax' => 0,
                    'total' => $lineTotal,
                    'payment_method' => 'purchase',
                    'source' => 'purchase',
                    'notes' => $data['notes'] ?? null,
                ]);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $variation->product_id,
                    'product_variation_id' => $variation->id,
                    'name' => $variation->product->name,
                    'color' => $variation->color,
                    'size' => $variation->size,
                    'quantity' => $data['quantity'],
                    'unit_price' => $unitPrice,
                    'total' => $lineTotal,
                    'sku' => $variation->sku,
                ]);
            } else {
                $variation->update([
                    'stock' => $data['quantity'],
                ]);
            }
        });

        return redirect()
            ->back()
            ->with('status', 'Inventory updated successfully.');
    }
}

