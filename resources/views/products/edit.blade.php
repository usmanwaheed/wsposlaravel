@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('products.update', $product) }}" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-lg bg-white p-6 shadow">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Edit {{ $product->name }}</h1>
                <p class="mt-1 text-sm text-gray-600">Update garment details, manage variations, and control barcode labels.</p>
            </div>
            <div class="text-sm text-gray-500">
                <p>Created {{ $product->created_at?->format('M d, Y') }}</p>
                <p>Last updated {{ $product->updated_at?->diffForHumans() }}</p>
            </div>
        </div>
    </div>

    @include('products.partials.form', ['product' => $product, 'variations' => $variations])

    <div class="flex justify-end">
        <a href="{{ route('products.index') }}" class="mr-3 inline-flex items-center rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50">Back</a>
        <button type="submit" class="inline-flex items-center rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Update product</button>
    </div>
</form>
@endsection
