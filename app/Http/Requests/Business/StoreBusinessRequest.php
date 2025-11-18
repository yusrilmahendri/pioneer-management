<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBusinessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->account_role, ['admin', 'owner']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'business' => [
                'required',
                'string',
                'max:255',
                'min:3',
                'unique:business,business'
            ],
            'start_date' => [
                'required',
                'date',
                'before_or_equal:today'
            ],
            'id_business_status' => [
                'required',
                'integer',
                'exists:business_status,id'
            ],
            'id_business_category' => [
                'required',
                'integer',
                'exists:business_category,id'
            ],
            'id_provinsi' => [
                'required',
                'integer',
                'min:1'
            ],
            'id_kabupaten' => [
                'required',
                'integer',
                'min:1'
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'address' => [
                'nullable',
                'string',
                'max:500'
            ],
            'phone' => [
                'nullable',
                'string',
                'regex:/^(\+62|62|0)[0-9]{9,13}$/'
            ],
            'email' => [
                'nullable',
                'email',
                'max:255'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'business.required' => 'Business name is required',
            'business.min' => 'Business name must be at least 3 characters',
            'business.max' => 'Business name must not exceed 255 characters',
            'business.unique' => 'Business name already exists',
            'start_date.required' => 'Start date is required',
            'start_date.date' => 'Start date must be a valid date',
            'start_date.before_or_equal' => 'Start date cannot be in the future',
            'id_business_status.required' => 'Business status is required',
            'id_business_status.exists' => 'Selected business status does not exist',
            'id_business_category.required' => 'Business category is required',
            'id_business_category.exists' => 'Selected business category does not exist',
            'id_provinsi.required' => 'Province is required',
            'id_kabupaten.required' => 'Regency/City is required',
            'phone.regex' => 'Invalid phone number format. Use Indonesian format (+62, 62, or 0)',
            'email.email' => 'Invalid email format'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'id_business_status' => 'business status',
            'id_business_category' => 'business category',
            'id_provinsi' => 'province',
            'id_kabupaten' => 'regency/city'
        ];
    }
}