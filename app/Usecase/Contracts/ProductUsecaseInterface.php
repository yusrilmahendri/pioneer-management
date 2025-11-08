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
}