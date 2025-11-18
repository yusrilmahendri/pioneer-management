<?php

namespace App\Http\Requests\Expenditure;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenditureRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
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
            'category' => [
                'required',
                'string',
                'max:255',
                Rule::in([
                    'operational', 'marketing', 'equipment', 'maintenance',
                    'office_supplies', 'transportation', 'utilities',
                    'professional_services', 'training', 'miscellaneous'
                ])
            ],
            'description' => [
                'required',
                'string',
                'max:1000',
                'min:10'
            ],
            'amount' => [
                'required',
                'numeric',
                'min:1000', // Minimum Rp 1,000
                'max:100000000' // Maximum Rp 100,000,000
            ],
            'receipt_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,pdf',
                'max:5120' // 5MB max
            ],
            'notes' => [
                'nullable',
                'string',
                'max:500'
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
            'category.required' => 'Expenditure category is required',
            'category.in' => 'Invalid expenditure category',
            'description.required' => 'Description is required',
            'description.min' => 'Description must be at least 10 characters',
            'description.max' => 'Description must not exceed 1000 characters',
            'amount.required' => 'Amount is required',
            'amount.numeric' => 'Amount must be a valid number',
            'amount.min' => 'Minimum expenditure amount is Rp 1,000',
            'amount.max' => 'Maximum expenditure amount is Rp 100,000,000',
            'receipt_image.image' => 'Receipt must be an image file',
            'receipt_image.mimes' => 'Receipt must be a JPEG, PNG, JPG, or PDF file',
            'receipt_image.max' => 'Receipt file size must not exceed 5MB',
            'notes.max' => 'Notes must not exceed 500 characters'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'id_business' => 'business',
            'receipt_image' => 'receipt'
        ];
    }
}