<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'customer' => ['nullable', 'array'],
            'customer.id' => ['nullable', 'exists:customers,id'],
            'customer.name' => ['required_without:customer.id', 'string', 'max:255'],
            'customer.email' => ['nullable', 'email'],
            'customer.phone' => ['nullable', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variation_id' => ['required', 'exists:product_variations,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'payment.method' => ['required', 'string', 'in:cash,card,credit'],
            'payment.amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'sync_to_woocommerce' => ['nullable', 'boolean'],
        ];
    }
}
