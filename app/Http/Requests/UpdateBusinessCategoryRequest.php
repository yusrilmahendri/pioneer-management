<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBusinessCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user has permission to update business categories
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $categoryId = $this->route('business_category') ?? $this->route('id');
        
        return [
            'name_category_business' => [
                'sometimes',
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('business_categories', 'name_category_business')
                    ->ignore($categoryId)
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name_category_business.required' => 'Business category name is required when provided.',
            'name_category_business.string' => 'Business category name must be a string.',
            'name_category_business.min' => 'Business category name must be at least 2 characters.',
            'name_category_business.max' => 'Business category name must not exceed 100 characters.',
            'name_category_business.unique' => 'This business category name already exists.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name_category_business' => 'business category name',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}