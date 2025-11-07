@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="text-lg font-semibold">Sales Summary</h2>
        <p class="mt-2 text-sm text-gray-600">Daily and monthly sales performance charts render via Vue components.</p>
    </div>
    <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="text-lg font-semibold">Inventory Alerts</h2>
        <p class="mt-2 text-sm text-gray-600">Monitor low stock variations and restock needs.</p>
    </div>
</div>
@endsection
