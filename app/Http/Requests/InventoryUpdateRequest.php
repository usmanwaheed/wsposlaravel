<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-products') ?? false;
    }

    public function rules(): array
    {
        return [
            'updates' => ['required', 'array', 'min:1'],
            'updates.*.product_variation_id' => ['required', 'exists:product_variations,id'],
            'updates.*.stock' => ['required_without:updates.*.delta', 'integer'],
            'updates.*.delta' => ['nullable', 'integer'],
        ];
    }
}
