<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isNestedUnderProduct = (bool) $this->route('product');

        return [
            'product_id' => $isNestedUnderProduct
                ? ['sometimes', 'integer', 'exists:products,id']
                : ['required', 'integer', 'exists:products,id'],
            'url' => ['required', 'string', 'max:2048'],
            'alt' => ['nullable', 'string', 'max:255'],
            'is_primary' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
