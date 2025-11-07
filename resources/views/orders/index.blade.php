@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="rounded-lg bg-white p-6 shadow">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Sales & purchase history</h1>
                <p class="mt-1 text-sm text-gray-600">Review POS sales, WooCommerce imports, and purchase orders generated from inventory replenishments.</p>
            </div>
            <form method="GET" class="flex flex-col gap-3 md:flex-row md:items-center">
                <select name="status" class="rounded border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                    <option value="">All statuses</option>
                    @foreach (['completed' => 'Completed', 'processing' => 'Processing', 'pending' => 'Pending', 'received' => 'Received'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="source" class="rounded border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                    <option value="">All sources</option>
                    @foreach (['pos' => 'POS', 'woocommerce' => 'WooCommerce', 'purchase' => 'Purchase'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['source'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="inline-flex items-center rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Filter</button>
            </form>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium uppercase tracking-wider text-gray-500">Order</th>
                    <th class="px-4 py-3 text-left font-medium uppercase tracking-wider text-gray-500">Customer</th>
                    <th class="px-4 py-3 text-left font-medium uppercase tracking-wider text-gray-500">Source</th>
                    <th class="px-4 py-3 text-left font-medium uppercase tracking-wider text-gray-500">Status</th>
                    <th class="px-4 py-3 text-right font-medium uppercase tracking-wider text-gray-500">Total</th>
                    <th class="px-4 py-3 text-right font-medium uppercase tracking-wider text-gray-500">Date</th>
                    <th class="px-4 py-3 text-right font-medium uppercase tracking-wider text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($orders as $order)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900">{{ $order->order_number }}</p>
                            <p class="mt-1 text-xs text-gray-500">Payment: {{ $order->payment_method }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $order->customer?->name ?? 'Walk-in customer' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ ucfirst($order->source) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ ucfirst($order->status) }}</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">${{ number_format($order->total, 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-500">{{ $order->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-3 text-sm">
                                <a href="{{ route('orders.show', $order) }}" class="font-semibold text-indigo-600 hover:text-indigo-500">View</a>
                                <a href="{{ route('orders.invoice', $order) }}" class="font-semibold text-gray-600 hover:text-gray-900">Invoice</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">No orders matched the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="border-t border-gray-200 px-4 py-3">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
