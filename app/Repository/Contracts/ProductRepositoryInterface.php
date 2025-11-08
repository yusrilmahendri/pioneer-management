<?php

namespace App\Repository\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function getAll(array $params = []): Collection;
    public function getPaginated(array $params = [], int $perPage = 15): LengthAwarePaginator;
    public function getById(int $id): ?Product;
    public function getByUuid(string $uuid): ?Product;
    public function getByUserId(string $userId): Collection;
    public function getByBusinessId(int $businessId): Collection;
    public function getByCategory(int $categoryId): Collection;
    public function getByStatus(int $statusId): Collection;
    public function create(array $data): Product;
    public function update(int $id, array $data): bool;
    public function updateByUuid(string $uuid, array $data): bool;
    public function delete(int $id): bool;
    public function deleteByUuid(string $uuid): bool;
    public function count(array $params = []): int;
    public function getWithRelations(array $relations = [], array $params = []): Collection;
}