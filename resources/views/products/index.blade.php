@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-6">
    <div class="flex flex-col justify-between gap-4 rounded-lg bg-white p-6 shadow md:flex-row md:items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Products</h1>
            <p class="mt-1 text-sm text-gray-600">Maintain garments, color/size variations, and barcode labels for the POS and WooCommerce catalog.</p>
        </div>
        <a href="{{ route('products.create') }}" class="inline-flex items-center rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Add Product</a>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div class="rounded-lg border border-gray-100 bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Products</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $products->total() }}</p>
            <p class="mt-1 text-xs text-gray-500">Across all garment categories.</p>
        </div>
        <div class="rounded-lg border border-gray-100 bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Low stock variations</p>
            <p class="mt-2 text-2xl font-semibold text-amber-600">{{ $lowStockCount }}</p>
            <p class="mt-1 text-xs text-gray-500">Trigger restocks via the inventory screen.</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Category / Brand</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Variations</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Total Stock</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($products as $product)
                    <tr>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900">{{ $product->name }}</p>
                            <p class="mt-1 text-xs text-gray-500">Base price {{ number_format($product->base_price, 2) }} · Tax {{ $product->tax_rate }}%</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-700">{{ $product->category }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ $product->brand ?: 'Unbranded' }}</p>
                        </td>
                        <td class="px-6 py-4 text-right text-sm text-gray-700">{{ $product->variations->count() }}</td>
                        <td class="px-6 py-4 text-right text-sm text-gray-700">{{ $product->variations->sum('stock') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-3 text-sm">
                                <a href="{{ route('products.edit', $product) }}" class="font-semibold text-indigo-600 hover:text-indigo-500">Edit</a>
                                <a href="{{ route('products.barcodes', $product) }}" class="font-semibold text-gray-600 hover:text-gray-900">Barcodes</a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Delete this product?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-semibold text-red-500 hover:text-red-600">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-6 text-center text-sm text-gray-500">No garments found. Add your first product to start selling.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="border-t border-gray-200 px-6 py-4">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection
