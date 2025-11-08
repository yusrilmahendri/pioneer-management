<?php

namespace App\Delivery\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Usecase\Contracts\ProductUsecaseInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    protected $productUsecase;

    public function __construct(ProductUsecaseInterface $productUsecase)
    {
        $this->productUsecase = $productUsecase;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $params = $this->buildQueryParams($request);
            
            if ($request->has('paginate') && $request->paginate === 'true') {
                $perPage = $request->get('per_page', 15);
                $result = $this->productUsecase->getPaginatedProducts($params, (int)$perPage);
            } else {
                $result = $this->productUsecase->getAllProducts($params);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $uuid): JsonResponse
    {
        try {
            $result = $this->productUsecase->getProductByUuid($uuid);
            
            if ($result['status'] === 'error') {
                return response()->json($result, 404);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();
            
            // Add current user ID if not provided (for employee creating their own products)
            if (!isset($payload['user_id'])) {
                $payload['user_id'] = Auth::user()->uuid;
            }
            
            $result = $this->productUsecase->createProduct($payload);

            return response()->json($result, 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        try {
            // Check ownership for employees
            if (Auth::user()->account_role === 'employee') {
                if (!$this->productUsecase->validateProductOwnership($uuid, Auth::user()->uuid)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'You can only update your own products'
                    ], 403);
                }
            }
            
            $result = $this->productUsecase->updateProduct($uuid, $request->all());
            
            if ($result['status'] === 'error') {
                return response()->json($result, 404);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $uuid): JsonResponse
    {
        try {
            // Check ownership for employees
            if (Auth::user()->account_role === 'employee') {
                if (!$this->productUsecase->validateProductOwnership($uuid, Auth::user()->uuid)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'You can only delete your own products'
                    ], 403);
                }
            }
            
            $result = $this->productUsecase->deleteProduct($uuid);
            
            if ($result['status'] === 'error') {
                return response()->json($result, 404);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while deleting product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getMyProducts(): JsonResponse
    {
        try {
            $userUuid = Auth::user()->uuid;
            $result = $this->productUsecase->getProductsByUser($userUuid);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving your products',
                'error' => $e->getMessage()
            ], 500);
        }
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
            $userUuid = Auth::user()->uuid;
            $result = $this->productUsecase->getProductStatistics($userUuid);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving statistics',
                'error' => $e->getMessage()
            ], 500);
        }
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

        // Always load relationships
        $params['with'] = ['categoryProduct', 'statusProduct', 'user'];

        return $params;
    }
}