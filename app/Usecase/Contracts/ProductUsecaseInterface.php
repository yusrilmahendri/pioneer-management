<?php

namespace App\Usecase\Contracts;

interface ProductUsecaseInterface
{
    public function getAllProducts(array $params = []): array;
    public function getPaginatedProducts(array $params = [], int $perPage = 15): array;
    public function getProductByUuid(string $uuid): array;
    public function getProductsByUser(string $userUuid): array;
    public function getProductsByBusiness(int $businessId): array;
    public function createProduct(array $payload): array;
    public function updateProduct(string $uuid, array $payload): array;
    public function deleteProduct(string $uuid): array;
    public function getProductStatistics(string $userUuid): array;
    public function validateProductOwnership(string $productUuid, string $userUuid): bool;
    public function validateOwnerProductAccess(string $productUuid, string $ownerUuid): bool;
    public function getProductsByOwner(string $ownerUuid, array $params = []): array;
    public function getProductsByEmployee(string $employeeUuid, array $params = []): array;
    public function validateEmployeeProductAccess(string $productUuid, string $employeeUuid): bool;
    public function getProductsBySupervisor(string $supervisorUuid, array $params = []): array;
    public function validateSupervisorProductAccess(string $productUuid, string $supervisorUuid): bool;
    public function getAdminProductStatistics(): array;
    public function getOwnerProductStatistics(string $ownerUuid): array;
}