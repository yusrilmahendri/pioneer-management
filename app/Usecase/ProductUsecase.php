<?php

namespace App\Usecase;

use App\Repository\Contracts\ProductRepositoryInterface;
use App\Repository\Contracts\UserRepositoryInterface;
use App\Usecase\Contracts\ProductUsecaseInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class ProductUsecase implements ProductUsecaseInterface
{
    protected $productRepository;
    protected $userRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->productRepository = $productRepository;
        $this->userRepository = $userRepository;
    }

    public function getAllProducts(array $params = []): array
    {
        $products = $this->productRepository->getWithRelations(['categoryProduct', 'statusProduct', 'user'], $params);

        return [
            'status' => 'success',
            'message' => 'Products retrieved successfully',
            'data' => $products->map(function ($product) {
                return $this->formatProductData($product);
            })->toArray()
        ];
    }

    public function getPaginatedProducts(array $params = [], int $perPage = 15): array
    {
        $params['with'] = ['categoryProduct', 'statusProduct', 'user'];
        $paginatedProducts = $this->productRepository->getPaginated($params, $perPage);

        return [
            'status' => 'success',
            'message' => 'Products retrieved successfully',
            'data' => $paginatedProducts->items(),
            'pagination' => [
                'current_page' => $paginatedProducts->currentPage(),
                'total_pages' => $paginatedProducts->lastPage(),
                'per_page' => $paginatedProducts->perPage(),
                'total_items' => $paginatedProducts->total(),
                'from' => $paginatedProducts->firstItem(),
                'to' => $paginatedProducts->lastItem()
            ]
        ];
    }

    public function getProductByUuid(string $uuid): array
    {
        $product = $this->productRepository->getByUuid($uuid);

        if (!$product) {
            return [
                'status' => 'error',
                'message' => 'Product not found',
                'data' => null
            ];
        }

        $product->load(['categoryProduct', 'statusProduct', 'user']);

        return [
            'status' => 'success',
            'message' => 'Product retrieved successfully',
            'data' => $this->formatProductData($product)
        ];
    }

    public function getProductsByUser(string $userUuid): array
    {
        $products = $this->productRepository->getByUserId($userUuid);
        
        return [
            'status' => 'success',
            'message' => 'Products retrieved successfully',
            'data' => $products->map(function ($product) {
                return $this->formatProductData($product);
            })->toArray()
        ];
    }

    public function getProductsByBusiness(int $businessId): array
    {
        $products = $this->productRepository->getByBusinessId($businessId);

        return [
            'status' => 'success',
            'message' => 'Products retrieved successfully',
            'data' => $products->map(function ($product) {
                return $this->formatProductData($product);
            })->toArray()
        ];
    }

    public function createProduct(array $payload): array
    {
        $validator = Validator::make($payload, [
            'name_product' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|string',
            'status_id' => 'required|string',
            'user_id' => 'required|exists:users,uuid',
            'stock' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        // UUID will be generated automatically by the model

        $product = $this->productRepository->create($payload);
        $product->load(['categoryProduct', 'statusProduct', 'user']);

        return [
            'status' => 'success',
            'message' => 'Product created successfully',
            'data' => $this->formatProductData($product)
        ];
    }

    public function updateProduct(string $uuid, array $payload): array
    {
        $product = $this->productRepository->getByUuid($uuid);

        if (!$product) {
            return [
                'status' => 'error',
                'message' => 'Product not found',
                'data' => null
            ];
        }

        $validator = Validator::make($payload, [
            'name_product' => 'sometimes|required|string|max:255',
            'deskripsi' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'category_id' => 'sometimes|required|string',
            'status_id' => 'sometimes|required|string',
            'stock' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $this->productRepository->updateByUuid($uuid, $payload);
        $updatedProduct = $this->productRepository->getByUuid($uuid);
        $updatedProduct->load(['categoryProduct', 'statusProduct', 'user']);

        return [
            'status' => 'success',
            'message' => 'Product updated successfully',
            'data' => $this->formatProductData($updatedProduct)
        ];
    }

    public function deleteProduct(string $uuid): array
    {
        $product = $this->productRepository->getByUuid($uuid);

        if (!$product) {
            return [
                'status' => 'error',
                'message' => 'Product not found',
                'data' => null
            ];
        }

        $this->productRepository->deleteByUuid($uuid);

        return [
            'status' => 'success',
            'message' => 'Product deleted successfully',
            'data' => null
        ];
    }

    public function getProductStatistics(string $userUuid): array
    {
        $totalProducts = $this->productRepository->count(['where' => ['user_id' => $userUuid]]);
        $activeProducts = $this->productRepository->count([
            'where' => [
                'user_id' => $userUuid,
                'status_product_id' => 1 // Assuming 1 is active status
            ]
        ]);

        return [
            'status' => 'success',
            'message' => 'Product statistics retrieved successfully',
            'data' => [
                'total_products' => $totalProducts,
                'active_products' => $activeProducts,
                'inactive_products' => $totalProducts - $activeProducts
            ]
        ];
    }

    public function validateProductOwnership(string $productUuid, string $userUuid): bool
    {
        $product = $this->productRepository->getByUuid($productUuid);
        return $product && $product->user_id === $userUuid;
    }

    protected function formatProductData($product): array
    {
        return [
            'uuid' => $product->uuid,
            'name' => $product->name_product,
            'description' => $product->deskripsi,
            'price' => $product->price,
            'stock' => $product->stock,
            'category' => $product->categoryProduct ? [
                'uuid' => $product->categoryProduct->uuid,
                'name' => $product->categoryProduct->name_category_product ?? 'Unknown'
            ] : null,
            'status' => $product->statusProduct ? [
                'uuid' => $product->statusProduct->uuid,
                'name' => $product->statusProduct->name_status_product ?? 'Unknown'
            ] : null,
            'user' => $product->user ? [
                'uuid' => $product->user->uuid,
                'name' => $product->user->name
            ] : null,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at
        ];
    }
}