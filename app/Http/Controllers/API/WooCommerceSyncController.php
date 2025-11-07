<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\WooCommerceService;
use Illuminate\Http\JsonResponse;

class WooCommerceSyncController extends Controller
{
    public function __construct(private WooCommerceService $woocommerce)
    {
    }

    public function syncProducts(): JsonResponse
    {
        $synced = $this->woocommerce->syncProducts();

        return response()->json(['synced' => $synced]);
    }

    public function syncInventory(): JsonResponse
    {
        $synced = $this->woocommerce->syncInventory();

        return response()->json(['synced' => $synced]);
    }

    public function syncOrders(): JsonResponse
    {
        $synced = $this->woocommerce->importOrders();

        return response()->json(['synced' => $synced]);
    }

    public function syncProduct(Product $product): JsonResponse
    {
        $result = $this->woocommerce->syncSingleProduct($product);

        return response()->json($result);
    }
}
