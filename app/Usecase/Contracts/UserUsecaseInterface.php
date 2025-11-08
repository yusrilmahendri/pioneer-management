<?php

namespace App\Usecase\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserUsecaseInterface
{
    /**
     * Get all users with optional filters
     */
    public function getAllUsers(array $params = []): array;

    /**
     * Get paginated users
     */
    public function getPaginatedUsers(array $params = [], int $perPage = 15): array;

    /**
     * Get user by UUID with formatted response
     */
    public function getUserByUuid(string $uuid): array;

    /**
     * Get user by email
     */
    public function getUserByEmail(string $email): array;

    /**
     * Get users by role
     */
    public function getUsersByRole(string $role): array;

    /**
     * Create new user with validation and business logic
     */
    public function createUser(array $payload): array;

    /**
     * Update user with validation
     */
    public function updateUser(string $uuid, array $payload): array;

    /**
     * Delete user
     */
    public function deleteUser(string $uuid): array;

    /**
     * User login authentication
     */
    public function loginUser(array $credentials): array;

    /**
     * Register new user
     */
    public function registerUser(array $payload): array;

    /**
     * Send forgot password token
     */
    public function forgotPassword(array $data): array;

    /**
     * Reset password using token
     */
    public function resetPassword(array $data): array;

    /**
     * Assign employee to business (new clean architecture method)
     */
    public function assignEmployeeToBusiness(array $data): array;

    /**
     * Remove employee from business
     */
    public function removeEmployeeFromBusiness(string $userUuid): array;

    /**
     * Get employees by business ID
     */
    public function getEmployeesByBusiness(int $businessId): array;

    /**
     * Get user dashboard data
     */
    public function getUserDashboardData(string $userUuid): array;

    /**
     * Change user password
     */
    public function changeUserPassword(string $userUuid, array $passwordData): array;

    /**
     * Get user profile with business information
     */
    public function getUserProfile(string $userUuid): array;

    /**
     * Update user profile
     */
    public function updateUserProfile(string $userUuid, array $profileData): array;

    /**
     * Calculate user statistics
     */
    public function calculateUserStatistics(string $userUuid): array;

    /**
     * Validate user permissions for specific action
     */
    public function validateUserPermission(string $userUuid, string $action): bool;

    /**
     * Validate role hierarchy for user creation
     */
    public function validateRoleHierarchy(string $currentUserRole, string $targetRole): array;
}