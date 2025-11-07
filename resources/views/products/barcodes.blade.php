@extends('layouts.app')

@push('styles')
<style>
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

        .label-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            gap: 12px !important;
        }

        .label-card {
            box-shadow: none !important;
            border: 1px solid #d1d5db !important;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 rounded-lg bg-white p-6 shadow md:flex-row md:items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Barcode labels · {{ $product->name }}</h1>
            <p class="mt-1 text-sm text-gray-600">Print CODE128 stickers for every color-size SKU using Zebra or ESC/POS label printers.</p>
        </div>
        <button type="button" class="no-print inline-flex items-center rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500" data-print>Print labels</button>
    </div>

    <div class="label-grid grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($labels as $label)
            <div class="label-card rounded border border-gray-200 bg-white p-4 shadow">
                <div class="text-sm font-semibold text-gray-900">{{ $product->name }}</div>
                <div class="mt-1 text-xs text-gray-500">{{ $label['variation']->color }} / {{ $label['variation']->size }}</div>
                <div class="mt-4 flex items-center justify-between text-sm text-gray-700">
                    <span>SKU {{ $label['variation']->sku }}</span>
                    <span>${{ number_format($label['variation']->price ?: $product->base_price, 2) }}</span>
                </div>
                <div class="mt-4">{!! $label['svg'] !!}</div>
                <p class="mt-2 text-xs text-gray-500">Barcode: {{ $label['code'] }}</p>
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const printBtn = document.querySelector('[data-print]');
        if (printBtn) {
            printBtn.addEventListener('click', () => window.print());
        }
    });
</script>
@endpush
