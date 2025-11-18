<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->account_role, ['admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->route('id');
        
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'min:2',
                'regex:/^[\pL\s\-\.\']+$/u'
            ],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'username' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                'min:3',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($userId)
            ],
            'password' => [
                'nullable',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
                'confirmed'
            ],
            'phone' => [
                'sometimes',
                'required',
                'string',
                'regex:/^(\+62|62|0)[0-9]{9,13}$/',
                Rule::unique('users', 'phone')->ignore($userId)
            ],
            'birth_of_date' => [
                'sometimes',
                'required',
                'date',
                'before:today',
                'after:1900-01-01'
            ],
            'birth_of_place' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'min:2'
            ],
            'gender' => [
                'sometimes',
                'required',
                'string',
                Rule::in(['male', 'female'])
            ],
            'start_date' => [
                'sometimes',
                'required',
                'date',
                'before_or_equal:today',
                'after_or_equal:2020-01-01'
            ],
            'end_date' => [
                'nullable',
                'date',
                'after:start_date'
            ],
            'placement' => [
                'sometimes',
                'required',
                'string',
                'max:255'
            ],
            'job_role' => [
                'sometimes',
                'required',
                'string',
                'max:255'
            ],
            'account_role' => [
                'sometimes',
                'required',
                'string',
                Rule::in(['admin', 'owner', 'supervisor', 'employee'])
            ],
            'salary' => [
                'sometimes',
                'required',
                'numeric',
                'min:2000000',
                'max:100000000'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Full name is required',
            'name.min' => 'Name must be at least 2 characters',
            'name.regex' => 'Name can only contain letters, spaces, hyphens, dots, and apostrophes',
            'email.required' => 'Email is required',
            'email.email' => 'Invalid email format',
            'email.unique' => 'Email already exists',
            'username.required' => 'Username is required',
            'username.min' => 'Username must be at least 3 characters',
            'username.alpha_dash' => 'Username can only contain letters, numbers, dashes, and underscores',
            'username.unique' => 'Username already exists',
            'phone.required' => 'Phone number is required',
            'phone.regex' => 'Invalid Indonesian phone number format',
            'phone.unique' => 'Phone number already exists',
            'birth_of_date.required' => 'Birth date is required',
            'birth_of_date.before' => 'Birth date must be before today',
            'birth_of_date.after' => 'Invalid birth year',
            'birth_of_place.required' => 'Birth place is required',
            'birth_of_place.min' => 'Birth place must be at least 2 characters',
            'gender.required' => 'Gender is required',
            'gender.in' => 'Gender must be male or female',
            'start_date.required' => 'Start date is required',
            'start_date.before_or_equal' => 'Start date cannot be in the future',
            'start_date.after_or_equal' => 'Start date cannot be before 2020',
            'end_date.after' => 'End date must be after start date',
            'placement.required' => 'Placement is required',
            'job_role.required' => 'Job role is required',
            'account_role.required' => 'Account role is required',
            'account_role.in' => 'Invalid account role',
            'salary.required' => 'Salary is required',
            'salary.min' => 'Minimum salary is Rp 2,000,000',
            'salary.max' => 'Maximum salary is Rp 100,000,000'
        ];
    }
}