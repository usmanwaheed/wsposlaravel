<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-products') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0'],
            'variations' => ['required', 'array', 'min:1'],
            'variations.*.color' => ['required', 'string', 'max:100'],
            'variations.*.size' => ['required', 'string', 'max:50'],
            'variations.*.sku' => ['required', 'string', 'max:191'],
            'variations.*.barcode' => ['nullable', 'string', 'max:191'],
            'variations.*.price' => ['nullable', 'numeric', 'min:0'],
            'variations.*.stock' => ['required', 'integer', 'min:0'],
        ];
    }
}
