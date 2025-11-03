<?php

namespace App\Http\Controllers\Authentivications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{   

    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'username' => 'required|string|max:255|unique:users,username',
                'password' => 'required|min:8|confirmed',
                'phone' => 'required|min:6',
                'birth_of_date' => 'required|date',
                'birth_of_place' => 'required|string|max:255',
                'gender' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'placement' => 'required|string|max:255',
                'job_role' => 'required|string|max:255',
                'account_role' => 'required|string|max:255',
                'salary' => 'nullable|numeric|min:0',
            ], [
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
                'phone.required' => 'Phone number is required.',
                'phone.min' => 'Phone number must be at least 6 characters long.',
                'birth_of_date.required' => 'Birth date is required.',
                'birth_of_date.date' => 'Please provide a valid birth date.',
                'birth_of_place.required' => 'Birth place is required.',
                'birth_of_place.string' => 'Birth place must be a string.',
                'birth_of_place.max' => 'Birth place must not exceed 255 characters.',
                'gender.required' => 'Gender field is required.',
                'start_date.required' => 'Start date is required.',
                'start_date.date' => 'Please provide a valid start date.',
                'end_date.date' => 'Please provide a valid end date.',
                'end_date.after_or_equal' => 'End date must be after or equal to start date.',
                'placement.required' => 'Placement field is required.',
                'placement.string' => 'Placement must be a string.',
                'placement.max' => 'Placement must not exceed 255 characters.',
                'job_role.required' => 'Job role is required.',
                'job_role.string' => 'Job role must be a string.',
                'job_role.max' => 'Job role must not exceed 255 characters.',
                'account_role.required' => 'Account role is required.',
                'account_role.string' => 'Account role must be a string.',
                'account_role.max' => 'Account role must not exceed 255 characters.',
                'salary.numeric' => 'Salary must be a number.',
                'salary.min' => 'Salary must be greater than or equal to 0.',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'],
                'birth_of_date' => $validated['birth_of_date'],
                'birth_of_place' => $validated['birth_of_place'],
                'gender' => $validated['gender'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'placement' => $validated['placement'],
                'job_role' => $validated['job_role'],
                'account_role' => $validated['account_role'],
                'salary' => $validated['salary'] ?? null,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully.',
                'user' => $user
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed. Please check the required fields.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'username_or_email' => ['required', 'string'],
            'password' => ['required'],
        ]);

        // Determine if the login input is an email or username
        $loginType = filter_var($request->username_or_email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        // Find user by email or username
        $user = User::where($loginType, $request->username_or_email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username_or_email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful.',
            'token' => $user->createToken('api-token')->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
            ]
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return response()->json([
            'status' => __($status)
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }

    public function pegawai(){
        return response()->json([
            'message' => 'Welcome, Pegawai!',
            'user' => auth()->user()
        ]);
    }
}
