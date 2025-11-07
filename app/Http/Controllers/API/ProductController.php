<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::with('variations')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($scoped) use ($request) {
                    $scoped->where('name', 'like', '%'.$request->string('search').'%')
                        ->orWhereHas('variations', function ($variationQuery) use ($request) {
                            $variationQuery->where('sku', 'like', '%'.$request->string('search').'%');
                        });
                });
            })
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($products);
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $data = $request->validated();

        $product = DB::transaction(function () use ($data) {
            $product = Product::create([
                'name' => $data['name'],
                'category' => $data['category'],
                'brand' => $data['brand'] ?? null,
                'description' => $data['description'] ?? null,
                'base_price' => $data['base_price'],
                'tax_rate' => $data['tax_rate'] ?? 0,
                'is_active' => true,
            ]);

            foreach ($data['variations'] as $variation) {
                $product->variations()->create([
                    'color' => $variation['color'],
                    'size' => $variation['size'],
                    'sku' => $variation['sku'],
                    'barcode' => $variation['barcode'] ?? null,
                    'price' => $variation['price'] ?? $product->base_price,
                    'stock' => $variation['stock'],
                ]);
            }

            return $product->load('variations');
        });

        return response()->json($product, 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json($product->load('variations'));
    }

    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $data = $request->validated();

        $product = DB::transaction(function () use ($data, $product) {
            $product->update([
                'name' => $data['name'],
                'category' => $data['category'],
                'brand' => $data['brand'] ?? null,
                'description' => $data['description'] ?? null,
                'base_price' => $data['base_price'],
                'tax_rate' => $data['tax_rate'] ?? 0,
            ]);

            $product->variations()->delete();

            foreach ($data['variations'] as $variation) {
                $product->variations()->create([
                    'color' => $variation['color'],
                    'size' => $variation['size'],
                    'sku' => $variation['sku'],
                    'barcode' => $variation['barcode'] ?? null,
                    'price' => $variation['price'] ?? $product->base_price,
                    'stock' => $variation['stock'],
                ]);
            }

            return $product->load('variations');
        });

        return response()->json($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(null, 204);
    }
}
