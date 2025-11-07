<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Picqer\Barcode\BarcodeGeneratorSVG;

class ProductManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manage-products');
    }

    public function index(): View
    {
        $products = Product::with('variations')->orderBy('name')->paginate(12);
        $lowStockCount = ProductVariation::where('stock', '<', 5)->count();

        return view('products.index', [
            'products' => $products,
            'lowStockCount' => $lowStockCount,
        ]);
    }

    public function create(): View
    {
        $product = new Product([
            'tax_rate' => 0,
            'base_price' => 0,
        ]);

        $variations = collect(old('variations', [
            ['color' => '', 'size' => '', 'sku' => '', 'barcode' => '', 'price' => '', 'stock' => 0],
        ]))->values()->all();

        return view('products.create', compact('product', 'variations'));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $product = DB::transaction(function () use ($data) {
            /** @var Product $product */
            $product = Product::create([
                'name' => $data['name'],
                'category' => $data['category'],
                'brand' => $data['brand'] ?? null,
                'description' => $data['description'] ?? null,
                'base_price' => $data['base_price'],
                'tax_rate' => $data['tax_rate'] ?? 0,
                'is_active' => true,
            ]);

            $this->syncVariations($product, $data['variations']);

            return $product;
        });

        return redirect()
            ->route('products.edit', $product)
            ->with('status', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $product->load('variations');

        $variations = collect(old('variations', $product->variations->map(function ($variation) {
            return [
                'id' => $variation->id,
                'color' => $variation->color,
                'size' => $variation->size,
                'sku' => $variation->sku,
                'barcode' => $variation->barcode,
                'price' => $variation->price,
                'stock' => $variation->stock,
            ];
        })->toArray()))->values()->all();

        return view('products.edit', compact('product', 'variations'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($product, $data) {
            $product->update([
                'name' => $data['name'],
                'category' => $data['category'],
                'brand' => $data['brand'] ?? null,
                'description' => $data['description'] ?? null,
                'base_price' => $data['base_price'],
                'tax_rate' => $data['tax_rate'] ?? 0,
                'is_active' => $product->is_active,
            ]);

            $this->syncVariations($product, $data['variations']);
        });

        return redirect()
            ->route('products.index')
            ->with('status', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('status', 'Product removed.');
    }

    public function barcodes(Product $product): View
    {
        $product->load('variations');
        $generator = new BarcodeGeneratorSVG();

        $labels = $product->variations->map(function (ProductVariation $variation) use ($generator) {
            $code = $variation->barcode ?: $variation->sku;

            return [
                'variation' => $variation,
                'code' => $code,
                'svg' => $generator->getBarcode($code, BarcodeGeneratorSVG::TYPE_CODE_128),
            ];
        });

        return view('products.barcodes', [
            'product' => $product,
            'labels' => $labels,
        ]);
    }

    protected function syncVariations(Product $product, array $variations): void
    {
        $keptIds = [];

        foreach ($variations as $variationData) {
            $payload = [
                'color' => $variationData['color'],
                'size' => $variationData['size'],
                'sku' => $variationData['sku'],
                'barcode' => $variationData['barcode'] ?? null,
                'price' => $variationData['price'] !== '' ? $variationData['price'] : null,
                'stock' => $variationData['stock'],
            ];

            if (! empty($variationData['id'])) {
                $existing = $product->variations()->whereKey($variationData['id'])->first();

                if ($existing) {
                    $existing->update($payload);
                    $keptIds[] = $existing->id;
                    continue;
                }
            }

            $created = $product->variations()->create($payload);
            $keptIds[] = $created->id;
        }

        $product->variations()->whereNotIn('id', $keptIds)->delete();
    }
}

