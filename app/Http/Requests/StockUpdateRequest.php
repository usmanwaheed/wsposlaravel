<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-products') ?? false;
    }

    public function rules(): array
    {
        $mode = $this->input('mode');

        $quantityRule = $mode === 'add'
            ? ['required', 'integer', 'min:1']
            : ['required', 'integer', 'min:0'];

        return [
            'mode' => ['required', Rule::in(['set', 'add'])],
            'quantity' => $quantityRule,
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}

