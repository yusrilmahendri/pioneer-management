<?php

namespace App\Delivery\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Public registration is allowed
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                Password::default()
            ],
            'password_confirmation' => 'required|string|min:8',
            'phone' => 'nullable|string|min:6',
            'birth_of_date' => 'nullable|date',
            'birth_of_place' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name field is required.',
            'name.string' => 'Name must be a string.',
            'name.max' => 'Name must not exceed 255 characters.',
            'email.required' => 'Email field is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered.',
            'username.required' => 'Username field is required.',
            'username.string' => 'Username must be a string.',
            'username.max' => 'Username must not exceed 255 characters.',
            'username.unique' => 'This username is already taken.',
            'password.required' => 'Password field is required.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password field confirmation does not match.',
            'password_confirmation.required' => 'Password confirmation field is required.',
            'password_confirmation.min' => 'Password confirmation must be at least 8 characters long.',
            'phone.min' => 'Phone number must be at least 6 characters long.',
            'birth_of_date.date' => 'Please provide a valid birth date.',
            'birth_of_place.string' => 'Birth place must be a string.',
            'birth_of_place.max' => 'Birth place must not exceed 255 characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Ensure we have the password_confirmation field
        if ($this->has('password') && !$this->has('password_confirmation')) {
            // If password_confirmation is missing, set it to empty to trigger validation error
            $this->merge([
                'password_confirmation' => ''
            ]);
        }

        // Set default account role for registration
        $this->merge([
            'account_role' => 'employee' // Default role for public registration
        ]);
    }

    /**
     * Get the validated data from the request.
     * Override to ensure we include account_role
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);
        
        // Add account_role for registration
        $validated['account_role'] = 'employee';
        
        return $validated;
    }
}