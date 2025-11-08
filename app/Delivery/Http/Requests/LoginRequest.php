<?php

namespace App\Delivery\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'username_or_email' => 'required|string',
            'password' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'username_or_email.required' => 'Email or username is required.',
            'password.required' => 'Password is required.'
        ];
    }
}