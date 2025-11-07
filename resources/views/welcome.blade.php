<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600 text-white">
    <div class="text-center">
        <h1 class="text-4xl font-bold">Garment Shop POS</h1>
        <p class="mt-4 text-lg">Unified POS and WooCommerce integration.</p>
        <div class="mt-6 flex justify-center gap-4">
            <a href="{{ route('login') }}" class="rounded bg-white px-6 py-3 font-semibold text-indigo-600">Sign In</a>
            <a href="{{ route('dashboard') }}" class="rounded border border-white px-6 py-3 font-semibold text-white">View Dashboard</a>
        </div>
    </div>
</body>
</html>
