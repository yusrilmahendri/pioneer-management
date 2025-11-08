<?php

namespace App\Delivery\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Add authorization logic if needed
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:8|confirmed',
            'account_role' => 'required|string|in:admin,owner,employee',
            'phone' => 'nullable|string|min:6',
            'birth_of_date' => 'nullable|date',
            'birth_of_place' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'placement' => 'nullable|string|max:255',
            'job_role' => 'nullable|string|max:255',
            'salary' => 'nullable|numeric|min:0',
            'business_id' => 'nullable|exists:add_busines,id'
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
            'password.confirmed' => 'Password confirmation does not match.',
            'account_role.required' => 'Account role is required.',
            'account_role.in' => 'Account role must be admin, owner, or employee.',
            'phone.min' => 'Phone number must be at least 6 characters long.',
            'birth_of_date.date' => 'Please provide a valid birth date.',
            'birth_of_place.string' => 'Birth place must be a string.',
            'birth_of_place.max' => 'Birth place must not exceed 255 characters.',
            'start_date.date' => 'Please provide a valid start date.',
            'end_date.date' => 'Please provide a valid end date.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'placement.string' => 'Placement must be a string.',
            'placement.max' => 'Placement must not exceed 255 characters.',
            'job_role.string' => 'Job role must be a string.',
            'job_role.max' => 'Job role must not exceed 255 characters.',
            'salary.numeric' => 'Salary must be a number.',
            'salary.min' => 'Salary must be greater than or equal to 0.',
            'business_id.exists' => 'Selected business does not exist.'
        ];
    }
}