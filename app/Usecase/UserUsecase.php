<?php

namespace App\Usecase;

use App\Repository\Contracts\UserRepositoryInterface;
use App\Usecase\Contracts\UserUsecaseInterface;
use App\Models\User;
use App\Models\BusinessAccount;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class UserUsecase implements UserUsecaseInterface
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAllUsers(array $params = []): array
    {
        $users = $this->userRepository->getWithRelations(['businesses.businessCategory', 'businesses.businessStatus'], $params);

        return [
            'status' => 'success',
            'message' => 'Users retrieved successfully',
            'data' => $users->map(function ($user) {
                return $this->formatUserData($user);
            })->toArray()
        ];
    }

    public function getPaginatedUsers(array $params = [], int $perPage = 15): array
    {
        $params['with'] = ['businesses.businessCategory', 'businesses.businessStatus'];
        $paginatedUsers = $this->userRepository->getPaginated($params, $perPage);

        return [
            'status' => 'success',
            'message' => 'Users retrieved successfully',
            'data' => $paginatedUsers->items(),
            'pagination' => [
                'current_page' => $paginatedUsers->currentPage(),
                'total_pages' => $paginatedUsers->lastPage(),
                'per_page' => $paginatedUsers->perPage(),
                'total_items' => $paginatedUsers->total(),
                'from' => $paginatedUsers->firstItem(),
                'to' => $paginatedUsers->lastItem()
            ]
        ];
    }

    public function getUserByUuid(string $uuid): array
    {
        $user = $this->userRepository->getByUuid($uuid);

        if (!$user) {
            return [
                'status' => 'error',
                'message' => 'User not found',
                'data' => null
            ];
        }

        $user->load(['businesses.businessCategory', 'businesses.businessStatus']);

        return [
            'status' => 'success',
            'message' => 'User retrieved successfully',
            'data' => $this->formatUserData($user)
        ];
    }

    public function getUserByEmail(string $email): array
    {
        $user = $this->userRepository->getByEmail($email);

        if (!$user) {
            return [
                'status' => 'error',
                'message' => 'User not found',
                'data' => null
            ];
        }

        return [
            'status' => 'success',
            'message' => 'User retrieved successfully',
            'data' => $this->formatUserData($user)
        ];
    }

    public function getUsersByRole(string $role): array
    {
        $users = $this->userRepository->getByRole($role);

        return [
            'status' => 'success',
            'message' => 'Users retrieved successfully',
            'data' => $users->map(function ($user) {
                return $this->formatUserData($user);
            })->toArray()
        ];
    }

    public function createUser(array $payload): array
    {
        // Validate input
        $validator = Validator::make($payload, [
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
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        // Validate role hierarchy if user is authenticated (not for public registration)
        if (auth()->check()) {
            $currentUser = auth()->user();
            $roleHierarchyCheck = $this->validateRoleHierarchy($currentUser->account_role, $payload['account_role']);
            
            if ($roleHierarchyCheck['status'] === 'error') {
                return $roleHierarchyCheck;
            }
        }

        // Hash password
        $payload['password'] = Hash::make($payload['password']);

        // Generate UUID
        $payload['uuid'] = (string) Str::uuid();

        // Create user
        $user = $this->userRepository->create($payload);

        return [
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => $this->formatUserData($user)
        ];
    }

    public function updateUser(string $uuid, array $payload): array
    {
        $user = $this->userRepository->getByUuid($uuid);

        if (!$user) {
            return [
                'status' => 'error',
                'message' => 'User not found',
                'data' => null
            ];
        }

        // Validate input
        $validator = Validator::make($payload, [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'username' => 'sometimes|required|string|max:255|unique:users,username,' . $user->id,
            'password' => 'sometimes|required|string|min:8|confirmed',
            'account_role' => 'sometimes|required|string|in:admin,owner,employee',
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
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        // Hash password if provided
        if (isset($payload['password'])) {
            $payload['password'] = Hash::make($payload['password']);
        }

        // Update user
        $this->userRepository->updateByUuid($uuid, $payload);

        $updatedUser = $this->userRepository->getByUuid($uuid);

        return [
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $this->formatUserData($updatedUser)
        ];
    }

    public function deleteUser(string $uuid): array
    {
        $user = $this->userRepository->getByUuid($uuid);

        if (!$user) {
            return [
                'status' => 'error',
                'message' => 'User not found',
                'data' => null
            ];
        }

        $this->userRepository->deleteByUuid($uuid);

        return [
            'status' => 'success',
            'message' => 'User deleted successfully',
            'data' => null
        ];
    }

    public function loginUser(array $credentials): array
    {
        $validator = Validator::make($credentials, [
            'username_or_email' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $user = $this->userRepository->getByEmailOrUsername($credentials['username_or_email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return [
                'status' => 'error',
                'message' => 'Invalid credentials',
                'data' => null
            ];
        }

        $user->load(['businesses.businessCategory', 'businesses.businessStatus']);
        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => $this->formatUserData($user)
            ]
        ];
    }

    public function registerUser(array $payload): array
    {
        return $this->createUser($payload);
    }

    public function forgotPassword(array $data): array
    {
        $validator = Validator::make($data, [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return [
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray()
            ];
        }

        $user = $this->userRepository->getByEmail($data['email']);

        if (!$user) {
            return [
                'status' => 'error',
                'message' => 'User with this email does not exist',
                'data' => null
            ];
        }

        // Generate password reset token
        $token = Str::random(60);
        
        // Store token in password_reset_tokens table
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $data['email']],
            [
                'email' => $data['email'],
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        // In a real application, you would send an email here
        // For now, we'll return the token (remove this in production)
        return [
            'status' => 'success',
            'message' => 'Password reset token generated successfully',
            'data' => [
                'reset_token' => $token, // Remove this in production
                'message' => 'Password reset instructions have been sent to your email'
            ]
        ];
    }

    public function resetPassword(array $data): array
    {
        $validator = Validator::make($data, [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return [
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray()
            ];
        }

        // Check if reset token exists and is valid
        $resetRecord = \DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->first();

        if (!$resetRecord) {
            return [
                'status' => 'error',
                'message' => 'Invalid or expired reset token',
                'data' => null
            ];
        }

        // Check if token matches
        if (!Hash::check($data['token'], $resetRecord->token)) {
            return [
                'status' => 'error',
                'message' => 'Invalid reset token',
                'data' => null
            ];
        }

        // Check if token is not expired (24 hours)
        if (now()->diffInHours($resetRecord->created_at) > 24) {
            // Delete expired token
            \DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
            
            return [
                'status' => 'error',
                'message' => 'Reset token has expired',
                'data' => null
            ];
        }

        // Find user and update password
        $user = $this->userRepository->getByEmail($data['email']);

        if (!$user) {
            return [
                'status' => 'error',
                'message' => 'User not found',
                'data' => null
            ];
        }

        // Update password
        $this->userRepository->updateByUuid($user->uuid, [
            'password' => Hash::make($data['password'])
        ]);

        // Delete the reset token
        \DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        // Revoke all existing tokens for security
        $user->tokens()->delete();

        return [
            'status' => 'success',
            'message' => 'Password has been reset successfully',
            'data' => null
        ];
    }

    public function getUserDashboardData(string $userUuid): array
    {
        $user = $this->userRepository->getByUuid($userUuid);

        if (!$user) {
            return [
                'status' => 'error',
                'message' => 'User not found',
                'data' => null
            ];
        }

        $user->load(['businesses.businessCategory', 'businesses.businessStatus']);

        return [
            'status' => 'success',
            'message' => 'Dashboard data retrieved successfully',
            'data' => [
                'user' => $this->formatUserData($user),
                'statistics' => $this->calculateUserStatistics($userUuid)['data']
            ]
        ];
    }

    public function changeUserPassword(string $userUuid, array $passwordData): array
    {
        $validator = Validator::make($passwordData, [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $user = $this->userRepository->getByUuid($userUuid);

        if (!$user) {
            return [
                'status' => 'error',
                'message' => 'User not found',
                'data' => null
            ];
        }

        if (!Hash::check($passwordData['current_password'], $user->password)) {
            return [
                'status' => 'error',
                'message' => 'Current password is incorrect',
                'data' => null
            ];
        }

        $this->userRepository->updateByUuid($userUuid, [
            'password' => Hash::make($passwordData['password'])
        ]);

        return [
            'status' => 'success',
            'message' => 'Password changed successfully',
            'data' => null
        ];
    }

    public function getUserProfile(string $userUuid): array
    {
        return $this->getUserByUuid($userUuid);
    }

    public function updateUserProfile(string $userUuid, array $profileData): array
    {
        // Remove sensitive fields that shouldn't be updated via profile
        unset($profileData['password'], $profileData['account_role'], $profileData['business_id']);

        return $this->updateUser($userUuid, $profileData);
    }

    public function calculateUserStatistics(string $userUuid): array
    {
        // This is where you'd implement business logic for calculating statistics
        // For example: total products, transactions, revenue, etc.
        
        return [
            'status' => 'success',
            'message' => 'Statistics calculated successfully',
            'data' => [
                'total_products' => 0, // Implement actual calculation
                'total_transactions' => 0,
                'total_revenue' => 0,
                'active_since' => null // User's start_date
            ]
        ];
    }

    public function validateUserPermission(string $userUuid, string $action): bool
    {
        $user = $this->userRepository->getByUuid($userUuid);

        if (!$user) {
            return false;
        }

        // Implement permission logic based on user role and action
        switch ($user->account_role) {
            case 'admin':
                return true; // Admin can do everything
            case 'owner':
                return in_array($action, ['manage_business', 'view_reports', 'manage_employees']);
            case 'employee':
                return in_array($action, ['manage_products', 'view_dashboard']);
            default:
                return false;
        }
    }

    /**
     * Validate role hierarchy for user creation
     * Admin can only create Owner
     * Owner can only create Employee
     * Employee cannot create anyone
     */
    public function validateRoleHierarchy(string $currentUserRole, string $targetRole): array
    {
        $allowedCreations = [
            'admin' => ['owner'],
            'owner' => ['employee'],
            'employee' => [] // Employee cannot create anyone
        ];

        if (!isset($allowedCreations[$currentUserRole])) {
            return [
                'status' => 'error',
                'message' => 'Invalid current user role',
                'data' => null
            ];
        }

        if (!in_array($targetRole, $allowedCreations[$currentUserRole])) {
            $roleHierarchy = [
                'admin' => 'Admin can only create Owner accounts',
                'owner' => 'Owner can only create Employee accounts', 
                'employee' => 'Employee cannot create any accounts'
            ];

            return [
                'status' => 'error',
                'message' => 'Role hierarchy violation: ' . $roleHierarchy[$currentUserRole],
                'data' => [
                    'current_role' => $currentUserRole,
                    'attempted_role' => $targetRole,
                    'allowed_roles' => $allowedCreations[$currentUserRole]
                ]
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Role hierarchy validated successfully',
            'data' => null
        ];
    }

    /**
     * Assign employee to business
     */
    public function assignEmployeeToBusiness(array $data): array
    {
        $validator = Validator::make($data, [
            'user_uuid' => 'required|exists:users,uuid',
            'business_id' => 'required|exists:business,id'
        ]);

        if ($validator->fails()) {
            return [
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray()
            ];
        }

        $user = $this->userRepository->getByUuid($data['user_uuid']);
        
        if (!$user) {
            return [
                'status' => 'error',
                'message' => 'User not found'
            ];
        }

        try {
            // Create entry in business_account pivot table
            BusinessAccount::create([
                'id_user' => $user->id,
                'id_business' => $data['business_id'],
                'created_by' => auth()->user()->uuid ?? null
            ]);

            // Reload user with business relationships
            $user = $this->userRepository->getByUuid($data['user_uuid']);

            return [
                'status' => 'success',
                'message' => 'Employee successfully assigned to business',
                'data' => $this->formatUserData($user)
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to assign employee to business: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Remove employee from business
     */
    public function removeEmployeeFromBusiness(string $userUuid): array
    {
        $user = $this->userRepository->getByUuid($userUuid);
        
        if (!$user) {
            return [
                'status' => 'error',
                'message' => 'User not found'
            ];
        }

        try {
            // Remove all entries from business_account pivot table for this user
            BusinessAccount::where('id_user', $user->id)->delete();

            // Reload user
            $user = $this->userRepository->getByUuid($userUuid);

            return [
                'status' => 'success',
                'message' => 'Employee successfully removed from business',
                'data' => $this->formatUserData($user)
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to remove employee from business: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get employees by business
     */
    public function getEmployeesByBusiness(int $businessId): array
    {
        try {
            // Get users through business_account pivot table
            $userIds = BusinessAccount::where('id_business', $businessId)->pluck('id_user');
            $employees = User::whereIn('id', $userIds)
                ->where('account_role', 'employee')
                ->get();

            return [
                'status' => 'success',
                'message' => 'Employees retrieved successfully',
                'data' => $employees->map(function($user) {
                    return $this->formatUserData($user);
                })
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve employees: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Format user data for consistent API responses
     */
    protected function formatUserData($user): array
    {
        return [
            'uuid' => $user->uuid,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'account_role' => $user->account_role,
            'phone' => $user->phone,
            'birth_of_date' => $user->birth_of_date,
            'birth_of_place' => $user->birth_of_place,
            'gender' => $user->gender,
            'start_date' => $user->start_date,
            'end_date' => $user->end_date,
            'placement' => $user->placement,
            'job_role' => $user->job_role,
            'salary' => $user->salary,
            'businesses' => $user->businesses ? $user->businesses->map(function($business) {
                return [
                    'id' => $business->id,
                    'name' => $business->business ?? '',
                    'business_category' => $business->businessCategory->business_category ?? null,
                    'business_status' => $business->businessStatus->business_status ?? null,
                    'start_date' => $business->start_date ?? null
                ];
            })->toArray() : [],
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at
        ];
    }
}