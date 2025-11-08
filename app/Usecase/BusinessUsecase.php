<?php

namespace App\Usecase;

use App\Repository\BusinessRepositoryInterface;
use App\Usecase\Contracts\BusinessUsecaseInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BusinessUsecase implements BusinessUsecaseInterface
{
    protected BusinessRepositoryInterface $businessRepository;

    public function __construct(BusinessRepositoryInterface $businessRepository)
    {
        $this->businessRepository = $businessRepository;
    }

    /**
     * Get all businesses with filters
     */
    public function getAllBusinesses(array $params): JsonResponse
    {
        try {
            $filters = [
                'search' => $params['search'] ?? null,
                'category_id' => $params['category_id'] ?? null,
                'status_id' => $params['status_id'] ?? null,
                'user_id' => $params['user_id'] ?? null,
                'order_by' => $params['order_by'] ?? 'created_at',
                'order_direction' => $params['order_direction'] ?? 'desc'
            ];

            $paginate = filter_var($params['paginate'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $perPage = (int) ($params['per_page'] ?? 10);

            $businesses = $this->businessRepository->getAll($filters, $paginate, $perPage);

            if ($paginate) {
                return response()->json([
                    'success' => true,
                    'message' => 'Businesses retrieved successfully',
                    'data' => $businesses->items(),
                    'pagination' => [
                        'current_page' => $businesses->currentPage(),
                        'last_page' => $businesses->lastPage(),
                        'per_page' => $businesses->perPage(),
                        'total' => $businesses->total(),
                        'from' => $businesses->firstItem(),
                        'to' => $businesses->lastItem()
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Businesses retrieved successfully',
                'data' => $businesses
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve businesses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get business by ID
     */
    public function getBusinessById(string $id): JsonResponse
    {
        try {
            $business = $this->businessRepository->findById($id);

            if (!$business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Business not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Business retrieved successfully',
                'data' => $business
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve business: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new business
     */
    public function createBusiness(array $data): JsonResponse
    {
        try {
            $validator = Validator::make($data, [
                'business' => 'required|string|max:255',
                'id_business_category' => 'required|exists:business_categories,id',
                'id_business_status' => 'required|exists:business_statuses,id',
                'description' => 'nullable|string',
                'address' => 'nullable|string',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email',
                'website' => 'nullable|url'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Add current user ID if not provided
            if (!isset($data['id_user'])) {
                $data['id_user'] = Auth::id();
            }

            $business = $this->businessRepository->create($data);

            return response()->json([
                'success' => true,
                'message' => 'Business created successfully',
                'data' => $business
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create business: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update business
     */
    public function updateBusiness(string $id, array $data): JsonResponse
    {
        try {
            $validator = Validator::make($data, [
                'business' => 'sometimes|required|string|max:255',
                'id_business_category' => 'sometimes|required|exists:business_categories,id',
                'id_business_status' => 'sometimes|required|exists:business_statuses,id',
                'description' => 'nullable|string',
                'address' => 'nullable|string',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email',
                'website' => 'nullable|url'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $business = $this->businessRepository->update($id, $data);

            if (!$business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Business not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Business updated successfully',
                'data' => $business
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update business: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete business
     */
    public function deleteBusiness(string $id): JsonResponse
    {
        try {
            $deleted = $this->businessRepository->delete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Business not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Business deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete business: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current user's businesses
     */
    public function getMyBusinesses(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $businesses = $this->businessRepository->getByUserId($userId);

            return response()->json([
                'success' => true,
                'message' => 'User businesses retrieved successfully',
                'data' => $businesses
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve businesses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get businesses by category
     */
    public function getBusinessesByCategory(int $categoryId): JsonResponse
    {
        try {
            $businesses = $this->businessRepository->getByCategory($categoryId);

            return response()->json([
                'success' => true,
                'message' => 'Businesses retrieved successfully',
                'data' => $businesses
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve businesses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get business statistics
     */
    public function getBusinessStatistics(): JsonResponse
    {
        try {
            $statistics = $this->businessRepository->getStatistics();

            return response()->json([
                'success' => true,
                'message' => 'Business statistics retrieved successfully',
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}