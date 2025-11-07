@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="rounded-lg bg-white p-6 shadow">
        <h1 class="text-2xl font-semibold text-gray-900">Inventory control</h1>
        <p class="mt-1 text-sm text-gray-600">Adjust on-hand quantities, record purchase orders, and keep the POS and WooCommerce storefront in sync.</p>
    </div>

    @if ($lowStock->isNotEmpty())
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-6 shadow">
            <h2 class="text-lg font-semibold text-amber-800">Low stock alerts</h2>
            <p class="mt-1 text-sm text-amber-700">The following variations are running low. Add stock using the controls below.</p>
            <div class="mt-4 grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($lowStock as $variation)
                    <div class="rounded border border-amber-200 bg-white p-4">
                        <p class="text-sm font-semibold text-gray-900">{{ $variation->product->name }}</p>
                        <p class="mt-1 text-xs text-gray-600">{{ $variation->color }} / {{ $variation->size }} · SKU {{ $variation->sku }}</p>
                        <p class="mt-2 text-sm font-medium text-amber-700">Stock: {{ $variation->stock }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium uppercase tracking-wider text-gray-500">Product</th>
                    <th class="px-4 py-3 text-left font-medium uppercase tracking-wider text-gray-500">Variation</th>
                    <th class="px-4 py-3 text-left font-medium uppercase tracking-wider text-gray-500">SKU</th>
                    <th class="px-4 py-3 text-right font-medium uppercase tracking-wider text-gray-500">Stock</th>
                    <th class="px-4 py-3 text-right font-medium uppercase tracking-wider text-gray-500">Update</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($variations as $variation)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $variation->product->name }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ $variation->product->category }} · {{ $variation->product->brand ?: 'Unbranded' }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $variation->color }} / {{ $variation->size }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $variation->sku }}</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">{{ $variation->stock }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('inventory.update', $variation) }}" class="flex flex-col items-end gap-2 md:flex-row md:items-center md:justify-end">
                                @csrf
                                @method('PATCH')
                                <select name="mode" class="rounded border border-gray-300 px-2 py-1 text-sm focus:border-indigo-500 focus:outline-none">
                                    <option value="set">Set exact</option>
                                    <option value="add">Add stock (purchase)</option>
                                </select>
                                <input type="number" name="quantity" min="0" value="0" class="w-24 rounded border border-gray-300 px-2 py-1 text-right focus:border-indigo-500 focus:outline-none" required>
                                <input type="text" name="notes" placeholder="Notes" class="w-full rounded border border-gray-300 px-2 py-1 text-sm focus:border-indigo-500 focus:outline-none md:w-48">
                                <button type="submit" class="inline-flex items-center rounded bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">Update</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="border-t border-gray-200 px-4 py-3">
            {{ $variations->links() }}
        </div>
    </div>
</div>
@endsection
