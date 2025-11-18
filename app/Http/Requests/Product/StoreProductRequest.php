<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->account_role, ['admin', 'owner', 'supervisor']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id_business' => [
                'required',
                'integer',
                'exists:business,id'
            ],
            'name_product' => [
                'required',
                'string',
                'max:255',
                'min:3'
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'price' => [
                'required',
                'numeric',
                'min:100', // Minimum Rp 100
                'max:1000000000' // Maximum Rp 1 Milyar
            ],
            'stock' => [
                'required',
                'integer',
                'min:0',
                'max:999999'
            ],
            'id_product_category' => [
                'required',
                'integer',
                'exists:product_category,id'
            ],
            'id_product_status' => [
                'required',
                'integer',
                'exists:product_status,id'
            ],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                'alpha_num:ascii'
            ],
            'weight' => [
                'nullable',
                'numeric',
                'min:0',
                'max:10000' // Maximum 10kg
            ],
            'dimensions' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^\d+(\.\d+)?\s*x\s*\d+(\.\d+)?\s*x\s*\d+(\.\d+)?\s*(cm|mm|m)?$/i'
            ],
            'image_url' => [
                'nullable',
                'url',
                'max:500'
            ],
            'min_stock' => [
                'nullable',
                'integer',
                'min:0',
                'max:999999'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'id_business.required' => 'Business ID is required',
            'id_business.exists' => 'Selected business does not exist',
            'name_product.required' => 'Product name is required',
            'name_product.min' => 'Product name must be at least 3 characters',
            'name_product.max' => 'Product name must not exceed 255 characters',
            'description.max' => 'Description must not exceed 1000 characters',
            'price.required' => 'Price is required',
            'price.numeric' => 'Price must be a valid number',
            'price.min' => 'Minimum product price is Rp 100',
            'price.max' => 'Maximum product price is Rp 1,000,000,000',
            'stock.required' => 'Stock quantity is required',
            'stock.integer' => 'Stock must be a valid integer',
            'stock.min' => 'Stock cannot be negative',
            'stock.max' => 'Maximum stock quantity is 999,999',
            'id_product_category.required' => 'Product category is required',
            'id_product_category.exists' => 'Selected product category does not exist',
            'id_product_status.required' => 'Product status is required',
            'id_product_status.exists' => 'Selected product status does not exist',
            'sku.alpha_num' => 'SKU must contain only letters and numbers',
            'sku.max' => 'SKU must not exceed 100 characters',
            'weight.numeric' => 'Weight must be a valid number',
            'weight.min' => 'Weight cannot be negative',
            'weight.max' => 'Maximum weight is 10,000 grams (10kg)',
            'dimensions.regex' => 'Dimensions format must be: length x width x height (e.g., 10 x 5 x 2 cm)',
            'image_url.url' => 'Image URL must be a valid URL',
            'min_stock.integer' => 'Minimum stock must be a valid integer',
            'min_stock.min' => 'Minimum stock cannot be negative'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'id_business' => 'business',
            'name_product' => 'product name',
            'id_product_category' => 'product category',
            'id_product_status' => 'product status',
            'min_stock' => 'minimum stock'
        ];
    }
}