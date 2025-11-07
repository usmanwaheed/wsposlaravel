@extends('layouts.app')

@push('styles')
<style>
    .thermal-paper {
        font-family: 'Courier New', Courier, monospace;
        width: 80mm;
        margin: 0 auto;
    }

    .thermal-paper table {
        width: 100%;
        border-collapse: collapse;
    }

    .thermal-paper td,
    .thermal-paper th {
        font-size: 12px;
        padding: 4px 0;
        text-align: left;
    }

    @media print {
        body {
            background: #fff !important;
        }

        nav, .no-print {
            display: none !important;
        }

        main {
            padding: 0 !important;
        }

        .thermal-wrapper {
            box-shadow: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <div class="no-print flex justify-between rounded-lg bg-white p-4 shadow">
        <a href="{{ route('orders.show', $order) }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">Back to order</a>
        <button type="button" class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500" data-print>Print invoice</button>
    </div>

    <div class="thermal-wrapper mx-auto rounded-lg bg-white p-6 shadow">
        <div class="thermal-paper">
            <div class="text-center">
                <p class="text-base font-semibold uppercase">Garment Shop POS</p>
                <p class="text-xs">{{ config('app.name') }}</p>
                <p class="mt-1 text-xs">{{ now()->format('M d, Y H:i') }}</p>
                <p class="text-xs">Cashier: {{ auth()->user()->name }}</p>
            </div>

            <hr class="my-3 border-gray-300" />

            <table>
                <thead>
                    <tr>
                        <th class="text-left text-xs font-semibold">Item</th>
                        <th class="text-right text-xs font-semibold">Qty</th>
                        <th class="text-right text-xs font-semibold">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>
                                <p class="text-xs font-semibold">{{ $item->name }}</p>
                                <p class="text-[10px] text-gray-500">{{ $item->color }} / {{ $item->size }} · SKU {{ $item->sku }}</p>
                            </td>
                            <td class="text-right text-xs">{{ $item->quantity }}</td>
                            <td class="text-right text-xs">${{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <hr class="my-3 border-gray-300" />

            <table>
                <tbody>
                    <tr>
                        <td class="text-xs">Subtotal</td>
                        <td class="text-right text-xs">${{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-xs">Discount</td>
                        <td class="text-right text-xs">-{{ number_format($order->discount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-xs">Tax</td>
                        <td class="text-right text-xs">${{ number_format($order->tax, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-xs font-semibold uppercase">Grand total</td>
                        <td class="text-right text-xs font-semibold">${{ number_format($order->total, 2) }}</td>
                    </tr>
                    @if ($order->payment)
                        <tr>
                            <td class="text-xs">Payment</td>
                            <td class="text-right text-xs">{{ ucfirst($order->payment->method) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <hr class="my-3 border-gray-300" />

            <p class="text-center text-[11px] text-gray-500">Thank you for shopping with us!</p>
            <p class="text-center text-[10px] text-gray-400">Order #{{ $order->order_number }} · {{ $order->created_at->format('M d, Y H:i') }}</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const printButton = document.querySelector('[data-print]');
        if (printButton) {
            printButton.addEventListener('click', () => window.print());
        }
    });
</script>
@endpush
