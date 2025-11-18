<?php

namespace App\Delivery\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseHandler;
use App\Usecase\Contracts\ProductUsecaseInterface;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    use ApiResponseHandler;
    
    protected $productUsecase;

    public function __construct(ProductUsecaseInterface $productUsecase)
    {
        $this->productUsecase = $productUsecase;
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        return $this->executeWithErrorHandling(function () use ($request) {
            $user = Auth::user();
            
            // Role-based product filtering
            $params = $this->buildQueryParams($request);
            
            $result = match ($user->account_role) {
                'admin' => $this->getAdminProducts($params, $request),
                'owner' => $this->getOwnerProducts($params, $request, $user),
                'supervisor' => $this->getSupervisorProducts($params, $request, $user),
                'employee' => $this->getEmployeeProducts($params, $request, $user),
                default => ['status' => 'error', 'message' => 'Unknown user role']
            };

            return $result;
        });
    }

    public function show(string $uuid): JsonResponse
    {
        return $this->executeWithErrorHandling(function () use ($uuid) {
            return $this->productUsecase->getProductByUuid($uuid);
        });
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        return $this->executeWithErrorHandling(function () use ($request) {
            $payload = $request->validated();
            
            // Add current user ID if not provided (for employee creating their own products)
            if (!isset($payload['user_id'])) {
                $payload['user_id'] = Auth::user()->uuid;
            }
            
            $result = $this->productUsecase->createProduct($payload);

            // Set success status code to 201 for created resource
            if ($result['status'] === 'success') {
                $result['code'] = 201;
            }
            
            return $result;
        });
    }

    public function update(UpdateProductRequest $request, string $uuid): JsonResponse
    {
        return $this->executeWithErrorHandling(function () use ($request, $uuid) {
            $user = Auth::user();
            
            // Role-based update permission
            switch ($user->account_role) {
                case 'admin':
                    // Admin can update any product
                    break;
                    
                case 'owner':
                    // Owner can update products from their businesses
                    if (!$this->productUsecase->validateOwnerProductAccess($uuid, $user->uuid)) {
                        return [
                            'status' => 'error',
                            'message' => 'You can only update products from your businesses'
                        ];
                    }
                    break;
                    
                case 'supervisor':
                    // Supervisor can update products in their assigned businesses
                    if (!$this->productUsecase->validateSupervisorProductAccess($uuid, $user->uuid)) {
                        return [
                            'status' => 'error',
                            'message' => 'You can only update products in your assigned businesses'
                        ];
                    }
                    break;
                    
                case 'employee':
                    // Employee can update products in their assigned business
                    if (!$this->productUsecase->validateEmployeeProductAccess($uuid, $user->uuid)) {
                        return [
                            'status' => 'error',
                            'message' => 'You can only update products in your assigned business'
                        ];
                    }
                    break;
                    
                default:
                    return [
                        'status' => 'error',
                        'message' => 'Unknown user role'
                    ];
            }
            
            return $this->productUsecase->updateProduct($uuid, $request->validated());
        });
    }

    public function destroy(string $uuid): JsonResponse
    {
        return $this->executeWithErrorHandling(function () use ($uuid) {
            $user = Auth::user();
            
            // Role-based delete permission
            switch ($user->account_role) {
                case 'admin':
                    // Admin can delete any product
                    break;
                    
                case 'owner':
                    // Owner can delete products from their businesses
                    if (!$this->productUsecase->validateOwnerProductAccess($uuid, $user->uuid)) {
                        return [
                            'status' => 'error',
                            'message' => 'You can only delete products from your businesses'
                        ];
                    }
                    break;
                    
                case 'supervisor':
                    // Supervisor can delete products in their assigned businesses
                    if (!$this->productUsecase->validateSupervisorProductAccess($uuid, $user->uuid)) {
                        return [
                            'status' => 'error',
                            'message' => 'You can only delete products in your assigned businesses'
                        ];
                    }
                    break;
                    
                case 'employee':
                    // Employee can delete products in their assigned business
                    if (!$this->productUsecase->validateEmployeeProductAccess($uuid, $user->uuid)) {
                        return [
                            'status' => 'error',
                            'message' => 'You can only delete products in your assigned business'
                        ];
                    }
                    break;
                    
                default:
                    return [
                        'status' => 'error',
                        'message' => 'Unknown user role'
                    ];
            }
            
            return $this->productUsecase->deleteProduct($uuid);
        });
    }

    public function getMyProducts(): JsonResponse
    {
        return $this->executeWithErrorHandling(function () {
            $userUuid = Auth::user()->uuid;
            return $this->productUsecase->getProductsByUser($userUuid);
        });
    }

    public function getProductsByBusiness(int $businessId): JsonResponse
    {
        try {
            $result = $this->productUsecase->getProductsByBusiness($businessId);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getStatistics(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Role-based statistics
            switch ($user->account_role) {
                case 'admin':
                    $result = $this->productUsecase->getAdminProductStatistics();
                    break;
                    
                case 'owner':
                    $result = $this->productUsecase->getOwnerProductStatistics($user->uuid);
                    break;
                    
                case 'employee':
                    $result = $this->productUsecase->getProductStatistics($user->uuid);
                    break;
                    
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Unknown user role'
                    ], 400);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products for admin (all products from all businesses)
     */
    protected function getAdminProducts(array $params, Request $request): array
    {
        if ($request->has('paginate') && $request->paginate === 'true') {
            $perPage = $request->get('per_page', 15);
            $result = $this->productUsecase->getPaginatedProducts($params, (int)$perPage);
        } else {
            $result = $this->productUsecase->getAllProducts($params);
        }

        return [
            'success' => true,
            'message' => 'Products retrieved successfully (Admin view)',
            'data' => $result
        ];
    }

    /**
     * Get products for owner (only from their businesses)
     */
    protected function getOwnerProducts(array $params, Request $request, $user): array
    {
        // Get owner's business IDs and filter products
        $result = $this->productUsecase->getProductsByOwner($user->uuid, $params);

        return [
            'success' => true,
            'message' => 'Products retrieved successfully (Owner view)',
            'data' => $result
        ];
    }

    /**
     * Get products for supervisor (all products in their assigned businesses with management rights)
     */
    protected function getSupervisorProducts(array $params, Request $request, $user): array
    {
        // Supervisor has same product access as employee but with management rights
        $result = $this->productUsecase->getProductsBySupervisor($user->uuid, $params);

        return [
            'success' => true,
            'message' => 'Business products retrieved successfully (Supervisor view)',
            'data' => $result
        ];
    }

    /**
     * Get products for employee (all products in their assigned business)
     */
    protected function getEmployeeProducts(array $params, Request $request, $user): array
    {
        // Get products from employee's business using usecase
        $result = $this->productUsecase->getProductsByEmployee($user->uuid, $params);

        return [
            'success' => true,
            'message' => 'Business products retrieved successfully (Employee view)',
            'data' => $result
        ];
    }

    protected function buildQueryParams(Request $request): array
    {
        $params = [];

        // Handle WHERE filters
        if ($request->has('category_id')) {
            $params['where']['category_id'] = $request->category_id;
        }

        if ($request->has('status_id')) {
            $params['where']['status_id'] = $request->status_id;
        }

        if ($request->has('user_id')) {
            $params['where']['user_id'] = $request->user_id;
        }

        // Handle LIKE filters
        if ($request->has('search')) {
            $params['like']['name_product'] = $request->search;
        }

        // Handle price range
        if ($request->has('min_price')) {
            $params['where'][] = ['price', '>=', $request->min_price];
        }

        if ($request->has('max_price')) {
            $params['where'][] = ['price', '<=', $request->max_price];
        }

        // Handle ordering
        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        $params['orderBy'][$orderBy] = $orderDirection;

        // Always load relationships including nested business relationships
        $params['with'] = [
            'categoryProduct', 
            'statusProduct', 
            'user',
            'user.business',
            'user.business.businessCategory'
        ];

        return $params;
    }
}