<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryUpdateRequest;
use App\Models\ProductVariation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class InventorySyncController extends Controller
{
    public function update(InventoryUpdateRequest $request): JsonResponse
    {
        $updates = $request->validated()['updates'];

        DB::transaction(function () use ($updates) {
            foreach ($updates as $payload) {
                /** @var ProductVariation $variation */
                $variation = ProductVariation::lockForUpdate()->findOrFail($payload['product_variation_id']);
                if (isset($payload['delta'])) {
                    $variation->increment('stock', $payload['delta']);
                } else {
                    $variation->update(['stock' => $payload['stock']]);
                }
            }
        });

        return response()->json(['status' => 'ok']);
    }
}
