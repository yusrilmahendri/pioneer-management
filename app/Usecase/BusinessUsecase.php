<?php

namespace App\Usecase;

use App\Repository\BusinessRepositoryInterface;
use App\Usecase\Contracts\BusinessUsecaseInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
    public function getAllBusinesses(array $filters = []): array
    {
        try {
            $filterParams = [
                'search' => $filters['search'] ?? null,
                'category_id' => $filters['category_id'] ?? null,
                'status_id' => $filters['status_id'] ?? null,
                'user_id' => $filters['user_id'] ?? null,
                'order_by' => $filters['order_by'] ?? 'created_at',
                'order_direction' => $filters['order_direction'] ?? 'desc'
            ];

            $businesses = $this->businessRepository->getAll($filterParams, false, 0);

            return [
                'status' => 'success',
                'message' => 'Businesses retrieved successfully',
                'data' => $businesses
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve businesses: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get paginated businesses
     */
    public function getPaginatedBusinesses(array $filters = [], int $perPage = 15): array
    {
        try {
            $filterParams = [
                'search' => $filters['search'] ?? null,
                'category_id' => $filters['category_id'] ?? null,
                'status_id' => $filters['status_id'] ?? null,
                'user_id' => $filters['user_id'] ?? null,
                'order_by' => $filters['order_by'] ?? 'created_at',
                'order_direction' => $filters['order_direction'] ?? 'desc'
            ];

            $businesses = $this->businessRepository->getAll($filterParams, true, $perPage);

            return [
                'status' => 'success',
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
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve businesses: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get business by ID
     */
    public function getBusinessById(string $id): array
    {
        try {
            $business = $this->businessRepository->findById($id);

            if (!$business) {
                return [
                    'status' => 'error',
                    'message' => 'Business not found',
                    'data' => null
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Business retrieved successfully',
                'data' => $business
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve business: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Create new business
     */
    public function createBusiness(array $data): array
    {
        $validator = Validator::make($data, [
            'business' => 'required|string|max:255',
            'id_business_category' => 'required|exists:business_category,id',
            'id_business_status' => 'required|exists:business_status,id',
            'id_user' => 'required|exists:users,id', // User (owner) that this business belongs to
            // 'description' => 'nullable|string',
            // 'address' => 'nullable|string',
            // 'phone' => 'nullable|string|max:20',
            // 'email' => 'nullable|email',
            // 'website' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        try {
            // Extract user_id from data (not part of business table)
            $userId = $data['id_user'];
            unset($data['id_user']);

            // Create the business
            $business = $this->businessRepository->create($data);

            // Assign the specified user to the business via pivot table
            \App\Models\BusinessAccount::create([
                'id_user' => $userId,
                'id_business' => $business->id,
                'created_by' => Auth::id() // The admin who created this assignment
            ]);

            // Reload business with relationships
            $business = $this->businessRepository->findById($business->id);

            return [
                'status' => 'success',
                'message' => 'Business created successfully',
                'data' => $business
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to create business: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update business
     */
    public function updateBusiness(string $id, array $data): array
    {
        $validator = Validator::make($data, [
            'business' => 'sometimes|required|string|max:255',
            'id_business_category' => 'sometimes|required|exists:business_category,id',
            'id_business_status' => 'sometimes|required|exists:business_status,id',
            // 'description' => 'nullable|string',
            // 'address' => 'nullable|string',
            // 'phone' => 'nullable|string|max:20',
            // 'email' => 'nullable|email',
            // 'website' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        try {
            $business = $this->businessRepository->update($id, $data);

            if (!$business) {
                return [
                    'status' => 'error',
                    'message' => 'Business not found',
                    'data' => null
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Business updated successfully',
                'data' => $business
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to update business: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Delete business
     */
    public function deleteBusiness(string $id): array
    {
        try {
            $deleted = $this->businessRepository->delete($id);

            if (!$deleted) {
                return [
                    'status' => 'error',
                    'message' => 'Business not found',
                    'data' => null
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Business deleted successfully',
                'data' => null
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to delete business: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get businesses by category
     */
    public function getBusinessesByCategory(int $categoryId): array
    {
        try {
            $businesses = $this->businessRepository->getByCategory($categoryId);

            return [
                'status' => 'success',
                'message' => 'Businesses retrieved successfully',
                'data' => $businesses
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve businesses: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get business statistics
     */
    public function getBusinessStatistics(): array
    {
        try {
            $statistics = $this->businessRepository->getStatistics();

            return [
                'status' => 'success',
                'message' => 'Business statistics retrieved successfully',
                'data' => $statistics
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve statistics: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get user's businesses
     */
    public function getUserBusinesses(int $userId): array
    {
        try {
            $businesses = $this->businessRepository->getByUserId($userId);

            return [
                'status' => 'success',
                'message' => 'User businesses retrieved successfully',
                'data' => $businesses
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve businesses: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
}