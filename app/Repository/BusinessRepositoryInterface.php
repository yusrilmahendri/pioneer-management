<?php

namespace App\Repository;

use App\Models\Business;

interface BusinessRepositoryInterface
{
    /**
     * Get all businesses with optional filters
     *
     * @param array $filters
     * @param bool $paginate
     * @param int $perPage
     * @return mixed
     */
    public function getAll(array $filters = [], bool $paginate = false, int $perPage = 10);

    /**
     * Find business by ID
     *
     * @param string $id
     * @return Business|null
     */
    public function findById(string $id): ?Business;

    /**
     * Create new business
     *
     * @param array $data
     * @return Business
     */
    public function create(array $data): Business;

    /**
     * Update existing business
     *
     * @param string $id
     * @param array $data
     * @return Business|null
     */
    public function update(string $id, array $data): ?Business;

    /**
     * Delete business
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool;

    /**
     * Get businesses by user ID
     *
     * @param int $userId
     * @return mixed
     */
    public function getByUserId(int $userId);

    /**
     * Get businesses by category
     *
     * @param int $categoryId
     * @return mixed
     */
    public function getByCategory(int $categoryId);

    /**
     * Get businesses by status
     *
     * @param int $statusId
     * @return mixed
     */
    public function getByStatus(int $statusId);

    /**
     * Get business statistics
     *
     * @return array
     */
    public function getStatistics(): array;
}