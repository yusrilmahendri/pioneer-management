<?php

namespace App\Usecase\Contracts;

use App\Models\Business;

interface BusinessUsecaseInterface
{
    /**
     * Get all businesses with optional filters
     *
     * @param array $filters
     * @return array
     */
    public function getAllBusinesses(array $filters = []): array;

    /**
     * Get paginated businesses
     *
     * @param array $filters
     * @param int $perPage
     * @return array
     */
    public function getPaginatedBusinesses(array $filters = [], int $perPage = 15): array;

    /**
     * Get business by ID
     *
     * @param string $id
     * @return array
     */
    public function getBusinessById(string $id): array;

    /**
     * Create new business
     *
     * @param array $data
     * @return array
     */
    public function createBusiness(array $data): array;

    /**
     * Update business
     *
     * @param string $id
     * @param array $data
     * @return array
     */
    public function updateBusiness(string $id, array $data): array;

    /**
     * Delete business
     *
     * @param string $id
     * @return array
     */
    public function deleteBusiness(string $id): array;

    /**
     * Get businesses by category
     *
     * @param int $categoryId
     * @return array
     */
    public function getBusinessesByCategory(int $categoryId): array;

    /**
     * Get business statistics
     *
     * @return array
     */
    public function getBusinessStatistics(): array;

    /**
     * Get user's businesses
     *
     * @param int $userId
     * @return array
     */
    public function getUserBusinesses(int $userId): array;
}