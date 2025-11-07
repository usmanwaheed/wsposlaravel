<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OrderManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view-reports');
    }

    public function index(Request $request): View
    {
        $orders = Order::with(['customer'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('source'), fn ($query) => $query->where('source', $request->string('source')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('orders.index', [
            'orders' => $orders,
            'filters' => $request->only(['status', 'source']),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['customer', 'items.variation.product', 'payment']);

        return view('orders.show', compact('order'));
    }

    public function invoice(Order $order): View
    {
        $order->load(['customer', 'items.variation.product', 'payment']);

        return view('orders.invoice', compact('order'));
    }
}

