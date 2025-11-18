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

    /**
     * Validate if owner can access this product (product from their businesses)
     */
    public function validateOwnerProductAccess(string $productUuid, string $ownerUuid): bool
    {
        $product = $this->productRepository->getByUuid($productUuid);
        
        if (!$product) {
            return false;
        }

        // Get owner's business IDs
        $owner = $this->userRepository->getByUuid($ownerUuid);
        if (!$owner) {
            return false;
        }

        $businessIds = \App\Models\Business::where('user_id', $owner->id)->pluck('id')->toArray();
        
        if (empty($businessIds)) {
            return false;
        }

        // Check if product creator belongs to owner's businesses
        $productCreator = $this->userRepository->getByUuid($product->user_id);
        
        return $productCreator && in_array($productCreator->business_id, $businessIds);
    }

    /**
     * Validate if employee can access this product (product from their assigned business)
     */
    public function validateEmployeeProductAccess(string $productId, string $employeeUuid): bool
    {
        // Find product by ID (not UUID since products use integer ID)
        $product = \App\Models\Product::find($productId);
        
        if (!$product) {
            return false;
        }

        // Get employee's assigned businesses from business_account pivot
        $employee = $this->userRepository->getByUuid($employeeUuid);
        if (!$employee) {
            return false;
        }

        $employeeBusinessIds = \DB::table('business_account')
            ->where('id_user', $employee->id)
            ->pluck('id_business')
            ->toArray();

        if (empty($employeeBusinessIds)) {
            return false;
        }

        // Check if product belongs to any of employee's businesses
        return in_array($product->id_business, $employeeBusinessIds);
    }

    /**
     * Get products for employee (from their assigned business)
     */
    public function getProductsByEmployee(string $employeeUuid, array $params = []): array
    {
        // Get employee's assigned businesses
        $employee = $this->userRepository->getByUuid($employeeUuid);
        
        if (!$employee) {
            return [
                'status' => 'error',
                'message' => 'Employee not found'
            ];
        }

        // Get employee's assigned businesses from business_account pivot
        $employeeBusinessIds = \DB::table('business_account')
            ->where('id_user', $employee->id)
            ->pluck('id_business')
            ->toArray();

        if (empty($employeeBusinessIds)) {
            return [
                'status' => 'success',
                'message' => 'No products found (Employee not assigned to any business)',
                'data' => []
            ];
        }

        // Filter products by business IDs directly (since products have id_business column)
        $params['where']['id_business'] = $employeeBusinessIds;

        $products = $this->productRepository->getWithRelations(['business', 'productCategory', 'productStatus'], $params);

        return [
            'status' => 'success',
            'message' => 'Business products retrieved successfully',
            'data' => $products->map(function ($product) {
                return $this->formatProductData($product);
            })->toArray()
        ];
    }

    /**
     * Get products for owner (from their businesses only)
     */
    public function getProductsByOwner(string $ownerUuid, array $params = []): array
    {
        // Get owner's business IDs
        $user = $this->userRepository->getByUuid($ownerUuid);
        
        if (!$user) {
            return [
                'status' => 'error',
                'message' => 'Owner not found'
            ];
        }

        // Get businesses owned by this user
        $businessIds = \App\Models\Business::where('user_id', $user->id)->pluck('id')->toArray();
        
        if (empty($businessIds)) {
            return [
                'status' => 'success',
                'message' => 'No products found (Owner has no businesses)',
                'data' => []
            ];
        }

        // Filter products by users who belong to owner's businesses
        $userIdsInBusinesses = \App\Models\User::whereIn('business_id', $businessIds)->pluck('uuid')->toArray();
        
        if (!empty($userIdsInBusinesses)) {
            $params['where']['user_id'] = $userIdsInBusinesses;
        } else {
            return [
                'status' => 'success',
                'message' => 'No products found (No employees in businesses)',
                'data' => []
            ];
        }

        $products = $this->productRepository->getWithRelations(['categoryProduct', 'statusProduct', 'user'], $params);

        return [
            'status' => 'success',
            'message' => 'Owner products retrieved successfully',
            'data' => $products->map(function ($product) {
                return $this->formatProductData($product);
            })->toArray()
        ];
    }

    /**
     * Get product statistics for admin (all products)
     */
    public function getAdminProductStatistics(): array
    {
        $totalProducts = $this->productRepository->count();
        $activeProducts = $this->productRepository->count([
            'where' => ['status_id' => 1] // Assuming 1 is active status
        ]);

        return [
            'status' => 'success',
            'message' => 'Admin product statistics retrieved successfully',
            'data' => [
                'total_products' => $totalProducts,
                'active_products' => $activeProducts,
                'inactive_products' => $totalProducts - $activeProducts,
                'view_type' => 'admin'
            ]
        ];
    }

    /**
     * Get product statistics for owner (from their businesses)
     */
    public function getOwnerProductStatistics(string $ownerUuid): array
    {
        $user = $this->userRepository->getByUuid($ownerUuid);
        
        if (!$user) {
            return [
                'status' => 'error',
                'message' => 'Owner not found'
            ];
        }

        // Get businesses owned by this user
        $businessIds = \App\Models\Business::where('user_id', $user->id)->pluck('id')->toArray();
        
        if (empty($businessIds)) {
            return [
                'status' => 'success',
                'message' => 'Owner product statistics (no businesses)',
                'data' => [
                    'total_products' => 0,
                    'active_products' => 0,
                    'inactive_products' => 0,
                    'view_type' => 'owner'
                ]
            ];
        }

        // Get products from employees in owner's businesses
        $userIdsInBusinesses = \App\Models\User::whereIn('business_id', $businessIds)->pluck('uuid')->toArray();
        
        if (empty($userIdsInBusinesses)) {
            return [
                'status' => 'success',
                'message' => 'Owner product statistics (no employees)',
                'data' => [
                    'total_products' => 0,
                    'active_products' => 0,
                    'inactive_products' => 0,
                    'view_type' => 'owner'
                ]
            ];
        }

        $totalProducts = $this->productRepository->count(['where' => [['user_id', 'IN', $userIdsInBusinesses]]]);
        $activeProducts = $this->productRepository->count([
            'where' => [
                ['user_id', 'IN', $userIdsInBusinesses],
                ['status_id', '=', 1]
            ]
        ]);

        return [
            'status' => 'success',
            'message' => 'Owner product statistics retrieved successfully',
            'data' => [
                'total_products' => $totalProducts,
                'active_products' => $activeProducts,
                'inactive_products' => $totalProducts - $activeProducts,
                'businesses_count' => count($businessIds),
                'view_type' => 'owner'
            ]
        ];
    }

    protected function formatProductData($product): array
    {
        // Get business information directly from product
        $business = null;

        if ($product->business) {
            $business = [
                'id' => $product->business->id,
                'name' => $product->business->business ?? 'Unknown Business'
            ];
        }

        return [
            'id' => $product->id,
            'name' => $product->product ?? 'Unknown Product', // product column name in DB
            'description' => $product->description,
            'price' => $product->price,
            'stock' => $product->stock,
            'category' => $product->productCategory ? [
                'id' => $product->productCategory->id,
                'name' => $product->productCategory->product_category ?? 'Unknown'
            ] : null,
            'status' => $product->productStatus ? [
                'id' => $product->productStatus->id,
                'name' => $product->productStatus->product_status ?? 'Unknown'
            ] : null,
            'business' => $business,
            'created_by' => $product->created_by,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at
        ];
    }

    /**
     * Get products for supervisor (from their assigned businesses with management rights)
     */
    public function getProductsBySupervisor(string $supervisorUuid, array $params = []): array
    {
        // Supervisor has same product access as employee but with management capabilities
        return $this->getProductsByEmployee($supervisorUuid, $params);
    }

    /**
     * Validate if supervisor can access this product (same logic as employee but with management rights)
     */
    public function validateSupervisorProductAccess(string $productId, string $supervisorUuid): bool
    {
        // Supervisor has same validation as employee
        return $this->validateEmployeeProductAccess($productId, $supervisorUuid);
    }
}