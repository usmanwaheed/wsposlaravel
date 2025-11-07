<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-100 text-gray-900">
    <div class="min-h-screen">
        <nav class="bg-white shadow">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center space-x-8">
                        <span class="text-xl font-semibold">{{ config('app.name') }}</span>
                        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Dashboard</a>
                        <a href="{{ route('pos.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">POS</a>
                        @can('manage-products')
                            <a href="{{ route('products.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Products</a>
                            <a href="{{ route('inventory.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Inventory</a>
                        @endcan
                        @can('view-reports')
                            <a href="{{ route('orders.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Orders</a>
                        @endcan
                    </div>
                    <div class="flex items-center space-x-4">
                        @auth
                            <span class="text-sm">{{ auth()->user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">Logout</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">Login</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <main class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                @if (session('status'))
                    <div class="mb-6 rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <p class="font-semibold">We ran into a few issues:</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
