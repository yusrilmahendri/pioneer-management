<?php

namespace App\Repository\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /**
     * Get all users with optional parameters
     */
    public function getAll(array $params = []): Collection;

    /**
     * Get paginated users
     */
    public function getPaginated(array $params = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get user by ID
     */
    public function getById(int $id): ?User;

    /**
     * Get user by UUID
     */
    public function getByUuid(string $uuid): ?User;

    /**
     * Get user by email
     */
    public function getByEmail(string $email): ?User;

    /**
     * Get user by username
     */
    public function getByUsername(string $username): ?User;

    /**
     * Get user by email or username (for login)
     */
    public function getByEmailOrUsername(string $identifier): ?User;

    /**
     * Get users by business ID
     */
    public function getByBusinessId(int $businessId): Collection;

    /**
     * Get users by role
     */
    public function getByRole(string $role): Collection;

    /**
     * Create new user
     */
    public function create(array $data): User;

    /**
     * Update user
     */
    public function update(int $id, array $data): bool;

    /**
     * Update user by UUID
     */
    public function updateByUuid(string $uuid, array $data): bool;

    /**
     * Delete user
     */
    public function delete(int $id): bool;

    /**
     * Delete user by UUID
     */
    public function deleteByUuid(string $uuid): bool;

    /**
     * Check if email exists
     */
    public function emailExists(string $email, ?int $excludeId = null): bool;

    /**
     * Check if username exists
     */
    public function usernameExists(string $username, ?int $excludeId = null): bool;

    /**
     * Count users with optional parameters
     */
    public function count(array $params = []): int;

    /**
     * Get users with relationships loaded
     */
    public function getWithRelations(array $relations = [], array $params = []): Collection;
}