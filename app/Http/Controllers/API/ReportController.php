<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function dailySales(): JsonResponse
    {
        $today = Carbon::today();

        $sales = Order::whereDate('created_at', $today)
            ->selectRaw('SUM(total) as total, SUM(discount) as discount, SUM(tax) as tax')
            ->first();

        return response()->json([
            'date' => $today->toDateString(),
            'total' => $sales->total ?? 0,
            'discount' => $sales->discount ?? 0,
            'tax' => $sales->tax ?? 0,
            'orders' => Order::whereDate('created_at', $today)->count(),
        ]);
    }

    public function monthlySales(): JsonResponse
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $sales = Order::whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as day, SUM(total) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return response()->json($sales);
    }

    public function productPerformance(): JsonResponse
    {
        $performance = OrderItem::select('product_variation_id')
            ->selectRaw('SUM(quantity) as quantity, SUM(total) as revenue')
            ->groupBy('product_variation_id')
            ->with(['variation.product'])
            ->orderByDesc('revenue')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'product' => $item->variation?->product?->name,
                    'sku' => $item->variation?->sku,
                    'quantity' => $item->quantity,
                    'revenue' => $item->revenue,
                ];
            });

        return response()->json($performance);
    }

    public function categoryPerformance(): JsonResponse
    {
        $categories = OrderItem::query()
            ->selectRaw('products.category, SUM(order_items.total) as revenue')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->groupBy('products.category')
            ->orderByDesc('revenue')
            ->get();

        return response()->json($categories);
    }

    public function lowStock(): JsonResponse
    {
        $variations = ProductVariation::where('stock', '<', 5)
            ->with('product')
            ->orderBy('stock')
            ->get();

        return response()->json($variations);
    }

    public function paymentSummary(): JsonResponse
    {
        $payments = DB::table('payments')
            ->select('method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('method')
            ->get();

        return response()->json($payments);
    }
}
