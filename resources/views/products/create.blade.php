@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('products.store') }}" class="space-y-6">
    @csrf

    <div class="rounded-lg bg-white p-6 shadow">
        <h1 class="text-2xl font-semibold text-gray-900">Add garment</h1>
        <p class="mt-1 text-sm text-gray-600">Create a product with color and size variations ready for barcode printing and POS sales.</p>
    </div>

    @include('products.partials.form', ['product' => $product, 'variations' => $variations])

    <div class="flex justify-end">
        <a href="{{ route('products.index') }}" class="mr-3 inline-flex items-center rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50">Cancel</a>
        <button type="submit" class="inline-flex items-center rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create product</button>
    </div>
</form>
@endsection
