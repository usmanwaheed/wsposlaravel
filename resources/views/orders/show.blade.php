@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="rounded-lg bg-white p-6 shadow">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Order {{ $order->order_number }}</h1>
                <p class="mt-1 text-sm text-gray-600">{{ ucfirst($order->source) }} · Status {{ ucfirst($order->status) }} · Created {{ $order->created_at->format('M d, Y H:i') }}</p>
            </div>
            <div class="flex gap-3 text-sm">
                <a href="{{ route('orders.invoice', $order) }}" class="inline-flex items-center rounded border border-indigo-200 px-4 py-2 font-semibold text-indigo-600 hover:bg-indigo-50">Invoice</a>
                <a href="{{ route('orders.index') }}" class="inline-flex items-center rounded border border-gray-200 px-4 py-2 font-semibold text-gray-600 hover:bg-gray-50">Back to orders</a>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-4 rounded-lg bg-white p-6 shadow lg:col-span-2">
            <h2 class="text-lg font-semibold text-gray-900">Items</h2>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium uppercase tracking-wider text-gray-500">Product</th>
                        <th class="px-4 py-2 text-left font-medium uppercase tracking-wider text-gray-500">Variation</th>
                        <th class="px-4 py-2 text-right font-medium uppercase tracking-wider text-gray-500">Qty</th>
                        <th class="px-4 py-2 text-right font-medium uppercase tracking-wider text-gray-500">Unit price</th>
                        <th class="px-4 py-2 text-right font-medium uppercase tracking-wider text-gray-500">Line total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="px-4 py-2">
                                <p class="font-medium text-gray-900">{{ $item->name }}</p>
                                <p class="mt-1 text-xs text-gray-500">SKU {{ $item->sku }}</p>
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-600">{{ $item->color }} / {{ $item->size }}</td>
                            <td class="px-4 py-2 text-right text-sm text-gray-700">{{ $item->quantity }}</td>
                            <td class="px-4 py-2 text-right text-sm text-gray-700">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-4 py-2 text-right text-sm font-semibold text-gray-900">${{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="space-y-4">
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="text-lg font-semibold text-gray-900">Customer</h2>
                <p class="mt-2 text-sm text-gray-700">{{ $order->customer?->name ?? 'Walk-in customer' }}</p>
                @if ($order->customer)
                    <p class="mt-1 text-xs text-gray-500">{{ $order->customer->email }} · {{ $order->customer->phone }}</p>
                    <p class="mt-2 text-xs text-gray-500">{{ $order->customer->address }}</p>
                @endif
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="text-lg font-semibold text-gray-900">Payment summary</h2>
                <dl class="mt-3 space-y-2 text-sm text-gray-700">
                    <div class="flex justify-between">
                        <dt>Subtotal</dt>
                        <dd>${{ number_format($order->subtotal, 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>Discount</dt>
                        <dd>-${{ number_format($order->discount, 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>Tax</dt>
                        <dd>${{ number_format($order->tax, 2) }}</dd>
                    </div>
                    <div class="flex justify-between text-lg font-semibold">
                        <dt>Total</dt>
                        <dd>${{ number_format($order->total, 2) }}</dd>
                    </div>
                    @if ($order->payment)
                        <div class="flex justify-between text-xs text-gray-500">
                            <dt>Paid</dt>
                            <dd>{{ $order->payment->method }} · {{ $order->payment->paid_at?->format('M d, Y H:i') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
